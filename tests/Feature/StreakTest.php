<?php

use App\Models\Assignment;
use App\Models\Family;
use App\Models\Streak;
use App\Models\User;
use Illuminate\Support\Carbon;

it('creates a streak with coherent family and assignment and casts dates/ints', function () {
    $family = Family::factory()->create();

    // Create an assignment for the family
    $assignment = Assignment::factory()->create([
        'family_id' => $family->id,
        'points' => 10,
    ]);

    // Create a streak for that assignment and family
    $streak = Streak::factory()->create([
        'family_id' => $family->id,
        'assignment_id' => $assignment->id,
        'current_streak' => 3,
        'best_streak' => 5,
        'streak_started_on' => now()->subDays(5)->toDateString(),
        'last_completed_on' => now()->toDateString(),
    ]);

    expect($streak->family->is($family))->toBeTrue()
        ->and($streak->assignment->is($assignment))->toBeTrue()
        ->and($streak->current_streak)->toBeInt()
        ->and($streak->best_streak)->toBeInt()
        ->and($streak->streak_started_on)->toBeInstanceOf(Carbon::class)
        ->and($streak->last_completed_on)->toBeInstanceOf(Carbon::class);
});

it('supports helpful scopes for family and assignment', function () {
    $family = Family::factory()->create();
    $otherFamily = Family::factory()->create();

    $assignmentA = Assignment::factory()->create(['family_id' => $family->id]);
    $assignmentB = Assignment::factory()->create(['family_id' => $family->id]);
    $otherAssignment = Assignment::factory()->create(['family_id' => $otherFamily->id]);

    $s1 = Streak::factory()->create(['family_id' => $family->id, 'assignment_id' => $assignmentA->id]);
    $s2 = Streak::factory()->create(['family_id' => $family->id, 'assignment_id' => $assignmentB->id]);
    $s3 = Streak::factory()->create(['family_id' => $otherFamily->id, 'assignment_id' => $otherAssignment->id]);

    $forFam = Streak::forFamily($family->id)->pluck('id')->all();
    $forAssignA = Streak::forAssignment($assignmentA->id)->pluck('id')->all();

    expect($forFam)->toEqualCanonicalizing([$s1->id, $s2->id])
        ->and($forAssignA)->toEqualCanonicalizing([$s1->id]);
});

it('factory helper forFamily keeps assignment in same family', function () {
    $family = Family::factory()->create();

    $streak = Streak::factory()->forFamily($family->id)->create();

    expect($streak->family_id)->toBe($family->id)
        ->and($streak->assignment->family_id)->toBe($family->id);
});
