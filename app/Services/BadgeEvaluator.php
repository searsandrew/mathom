<?php

namespace App\Services;

use App\Events\BadgeAwarded;
use App\Models\Badge;
use App\Models\Ledger;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class BadgeEvaluator
{
    public function afterSubmissionApproved(User $user): void
    {
        $this->evaluateAll($user);
    }

    public function afterRedemptionFulfilled(User $user): void
    {
        $this->evaluateAll($user);
    }

    public function afterAllowancePaid(User $user): void
    {
        $this->evaluateAll($user);
    }

    protected function evaluateAll(User $user): void
    {
        // In unit tests or edge cases, a non-persisted User should be ignored to avoid unintended DB queries
        if (!$user->exists) return;

        $familyId = $user->family()?->id ?? $user->wallet?->family_id;
        if (!$familyId) return;

        $badges = Badge::query()
            ->where('family_id', $familyId)
            ->get();

        foreach ($badges as $badge) {
            if ($this->alreadyHas($user, $badge)) continue;
            if ($this->meetsCriteria($user, $badge)) {
                DB::transaction(function () use ($user, $badge, $familyId) {
                    if ($this->alreadyHas($user, $badge)) return;
                    $user->badges()->attach($badge->id, [
                        'awarded_at'    => now(),
                        'reason'        => 'Criteria met',
                    ]);

                    event(new BadgeAwarded($badge));
                });
            }
        }
    }

    protected function alreadyHas(User $user, Badge $badge): bool
    {
        return $user->badges()->whereKey($badge->id)->exists();
    }

    protected function meetsCriteria(User $user, Badge $badge): bool
    {
        $criteria = $badge->criteria ?? [];
        if (isset($criteria['lifetime_points']['gte'])) {
            $walletId = $user->wallet?->id;
            if (!$walletId) return false;

            $sum = Ledger::query()
                ->where('wallet_id', $walletId)
                ->whereIn('type', ['earn', 'bonus', 'manual_adjust', 'allowance_payout'])
                ->sum('amount');

            if ($sum < (int) $criteria['lifetime_points']['gte']) return false;
        }

        return true;
    }
}
