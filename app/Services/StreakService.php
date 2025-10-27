<?php

namespace App\Services;

use App\Models\Occurrence;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StreakService
{
    public function onOccurrenceApproved(Occurrence $occurrence, User $user): void
    {
        DB::transaction(function () use ($occurrence, $user) {
            $streak = Streak::firstOrCreate([
                'family_id' => $occurrence->family_id,
                'assignment_id' => $occurrence->assignment_id,
                'user_id' => $user->id,
            ]);

            $today = $occurrence->due_on;
            $prev = $streak->last_completed_on;

            $inSeq = $prev ? $prev->copy()->addDay()->equalTo($today) : true;

            $streak->current_streak = $inSeq ? ($streak->current_streak + 1) : 1;
            $streak->last_completed_on = $today;
            $streak->streak_started_on = $inSeq && $streak->streak_started_on ? $streak->streak_started_on : $today;
            $streak->best_streak = max($streak->best_streak, $streak->current_streak);
            $streak->save();
        });
    }
}
