<?php

namespace App\Services;

use App\Events\AllowanceDue;
use App\Models\Allowance;
use App\Models\AllowanceRun;
use App\Models\Ledger;
use Illuminate\Container\Attributes\DB;
use Illuminate\Contracts\Queue\ShouldQueue;

class ProcessAllowance implements ShouldQueue
{
    public function handle(AllowanceDue $event): void
    {
        $allowance = $event->allowance->load('wallet');

        DB::transaction(function () use ($allowance, $event) {
            $run = AllowanceRun::firstOrCreate([
                'allowance_id'  => $allowance->id,
                'started_at'  => $event->periodStart->toDateString(),
                'ended_at'    => $event->periodEnd->toDateString(),
            ]);

            if ($run->status === 'paid') return;

            $points = $this->computePoints($allowance, $event);

            $run->calculated_points = $points;
            $run->status = $points > 0 ? 'calculated' : 'skipped';
            $run->calc_summary = ['mode' => $allowance->mode];
            $run->save();

            if ($points > 0) {
                $entry = Ledger::create([
                    'family_id'     => $allowance->family_id,
                    'wallet_id'     => $allowance->wallet_id,
                    'occurred_at'   => now(),
                    'type'          => 'allowance_payout',
                    'amount'        => $points,
                    'reference_type' => 'allowance_run',
                    'reference_id'  => $run->id,
                    'metadata'      => [],
                ]);
                $allowance->wallet->increment('balance', $points);
                $run->update(['status' => 'paid', 'ledger_id' => $entry->id])
            }
        });
    }

    private function computePoints(Allowance $allowance, AllowanceDue $event): int
    {
        return (int)($allowance->fixed_points ?? 0);
    }
}
