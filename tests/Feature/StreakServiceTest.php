<?php

use App\Models\Assignment;
use App\Models\Occurrence;
use App\Models\Streak;
use App\Models\User;
use App\Services\StreakService;
use Illuminate\Support\Carbon;

it('creates a new streak on first approval and sets streak fields', function () {
    $user = User::factory()->create();
    $assignment = Assignment::factory()->forUser($user)->create();

    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-20'),
        'status' => 'pending',
    ]);

    (new StreakService())->onOccurrenceApproved($occurrence, $user);

    $streak = Streak::where('assignment_id', $assignment->id)->where('user_id', $user->id)->first();
    expect($streak)->not()->toBeNull()
        ->and($streak->current_streak)->toBe(1)
        ->and($streak->best_streak)->toBe(1)
        ->and($streak->last_completed_on->toDateString())->toBe('2025-10-20')
        ->and($streak->streak_started_on->toDateString())->toBe('2025-10-20');
});

it('increments current streak on consecutive day and updates best', function () {
    $user = User::factory()->create();
    $assignment = Assignment::factory()->forUser($user)->create();

    $svc = new StreakService();

    $d1 = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-20'),
        'status' => 'pending',
    ]);
    $svc->onOccurrenceApproved($d1, $user);

    $d2 = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-21'),
        'status' => 'pending',
    ]);
    $svc->onOccurrenceApproved($d2, $user);

    $streak = Streak::where('assignment_id', $assignment->id)->where('user_id', $user->id)->first();
    expect($streak->current_streak)->toBe(2)
        ->and($streak->best_streak)->toBe(2)
        ->and($streak->streak_started_on->toDateString())->toBe('2025-10-20')
        ->and($streak->last_completed_on->toDateString())->toBe('2025-10-21');
});

it('resets current streak when non-consecutive and keeps best', function () {
    $user = User::factory()->create();
    $assignment = Assignment::factory()->forUser($user)->create();
    $svc = new StreakService();

    $svc->onOccurrenceApproved(Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-20'),
    ]), $user);
    $svc->onOccurrenceApproved(Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-21'),
    ]), $user);

    // Gap day (23 vs expected 22) should reset current to 1, best stays 2
    $svc->onOccurrenceApproved(Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-23'),
    ]), $user);

    $streak = Streak::where('assignment_id', $assignment->id)->where('user_id', $user->id)->first();
    expect($streak->current_streak)->toBe(1)
        ->and($streak->best_streak)->toBe(2)
        ->and($streak->streak_started_on->toDateString())->toBe('2025-10-23')
        ->and($streak->last_completed_on->toDateString())->toBe('2025-10-23');
});
