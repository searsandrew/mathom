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

    public function afterRedeptionFulfilled(User $user): void
    {
        $this->evaluateAll($user);
    }

    public function afterAllowancePaid(User $user): void
    {
        $this->evaluateAll($user);
    }

    protected function evaluateAll(User $user): void
    {
        $badges = Badge::query()->where('family_id', $user->family->id)-> where('is_active', true)->get();

        foreach ($badges as $badge) {
            if ($this->alreadyHas($user, $badge)) continue;
            if ($this->meetsCriteria($user, $badge)) {
                DB::transaction(function () use ($user, $badge) {
                    if($this->alreadyHas($user, $badge)) return;
                    $user->badges()->attach($badge->id, [
                        'family_id'     => $user->family_id,
                        'awarded_at'    => now(),
                        'reason'        => 'Criteria met'
                    ]);

                    event(new BadgeAwarded($badge));
                });
            }
        }
    }

    protected function alreadyHas(User $user, Badge $badge): bool
    {
        return Badge::where('badge_id', $badge->id)->where('user_id', $user->id)->exists();
    }

    protected function meetsCriteria(User $user, Badge $badge): bool
    {
        $criteria = $badge->criteria ?? [];
        if (isset($criteria['lifetime_points']['gte'])) {
            $sum = Ledger::query()
                ->where('wallet_id', $user->wallet->id)
                ->whereIn('type', ['earn', 'bonus', 'manual_adjust', 'allowance_payout'])
                ->sum('amount');

            if ($sum < (int) $criteria['lifetime_points']['gte']) return false;
        }

        return true;
    }
}
