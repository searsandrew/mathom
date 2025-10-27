<?php

namespace App\Services;

use App\Models\Ledger;
use App\Models\Occurrence;
use App\Models\Redemption;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class LedgerService
{
    public function earnForOccurrence(Submission $submission, Occurrence $occurrence, User $user, int $points): void
    {
        DB::transaction(function () use ($submission, $occurrence, $user, $points) {
            $exists = Ledger::query()
                ->where('reference_type', 'occurrence')
                ->where('reference_id', $occurrence->getKey())
                ->where('type', 'earn')
                ->exists();

            if ($exists) return;

            Ledger::create([
                'family_id'     => $occurrence->family_id,
                'wallet_id'     => $user->wallet->id,
                'occurrence_at' => now(),
                'type'          => 'earn',
                'amount'        => $points,
                'reference_type' => 'occurrence',
                'reference_id'  => $occurrence->getKey(),
                'metadata'      => ['submission_id' => $submission->getKey()],
            ]);

            $user->wallet->increment('balance', $points);

            $occurrence->update(['points_awarded' => $points, 'status' => 'approved']);
        });
    }

    public function placeHoldForRedemption(Redemption $redemption): void
    {
        DB::transaction(function () use ($redemption) {
            $total = $redemption->items()->sum('total_points');

            $exists = $redemption->ledgerHold()->exists();
            if ($exists) return;

            $entry = Ledger::create([
                'family_id'     => $redemption->family_id,
                'wallet_id'     => $redemption->wallet_id,
                'occurred_at' => now(),
                'type'          => 'redeem_hold',
                'amount'        => -$total,
                'reference_type' => 'redemption',
                'reference_id'  => $redemption->getKey(),
                'metadata'      => ['status' => $redemption->status],
            ]);

            $redemption->update(['hold_ledger_id' => $entry->getKey()]);
            $redemption->wallet->decrement('balance', $total);
        });
    }

    public function releaseHoldForRedemption(Redemption $redemption, ?string $reason = null): void
    {
        DB::transaction(function () use ($redemption, $reason) {
            $total = $redemption->items()->sum('total_points');

            $released = $redemption->ledgerReleases()->exists();
            if ($released) return;

            Ledger::create([
                'family_id'     => $redemption->family_id,
                'wallet_id'     => $redemption->wallet_id,
                'occurred_at'   => now(),
                'type'          => 'redeem_release',
                'amount'        => $total,
                'reference_type' => 'redemption',
                'reference_id'  => $redemption->getKey(),
                'metadata'      => ['reason' => $reason],
            ]);

            $redemption->wallet->increment('balance', $total);
        });
    }

    public function captureHoldForRedemption(Redemption $redemption): void
    {
        DB::transaction(function () use ($redemption) {
            if ($redemption->ledgerCaptures()->exists()) return;

            Ledger::create([
                'family_id'     => $redemption->family_id,
                'wallet_id'     => $redemption->wallet_id,
                'occurred_at'   => now(),
                'type'          => 'redeem_capture',
                'amount'        => 0,
                'reference_type' => 'redemption',
                'reference_id'  => $redemption->getKey(),
                'metadata'      => [],
            ]);
        });
    }
}
