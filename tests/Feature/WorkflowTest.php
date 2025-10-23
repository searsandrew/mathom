<?php

use App\Models\Assignment;
use App\Models\Chore;
use App\Models\Family;
use App\Models\Occurrence;
use App\Models\Reward;
use App\Models\Redemption;
use App\Models\Submission;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Support\Carbon;

it('can create core workflow entities and maintain relationships', function () {
    $family = Family::factory()->create();

    // Create users and attach to family with roles
    $parent = User::factory()->create();
    $child = User::factory()->create();
    $family->users()->attach([$parent->id => ['role' => 'parent', 'is_admin' => true]]);
    $family->users()->attach([$child->id => ['role' => 'child', 'is_admin' => false]]);

    // Create a wallet for the child in the same family
    $wallet = Wallet::factory()->create([
        'family_id' => $family->id,
        'user_id' => $child->id,
        'balance' => 0,
    ]);

    // Create a chore for the family
    $chore = Chore::factory()->create([
        'family_id' => $family->id,
        'points' => 10,
    ]);

    // Create an assignment for the chore in the same family and assigned to the child
    $assignment = Assignment::factory()
        ->forUser($child)
        ->create([
            'family_id' => $family->id,
            'chore_id' => $chore->id,
            'points' => 10,
        ]);

    // Create an occurrence for the assignment in the same family
    $occurrence = Occurrence::factory()->create([
        'family_id' => $family->id,
        'assignment_id' => $assignment->id,
        'points_awarded' => 10,
    ]);

    // Create a submission by the child for the occurrence
    $submission = Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $child->id,
        'submitted_at' => now(),
        'status' => 'pending',
    ]);

    // Create a reward and a redemption tied to the same family and wallet
    $reward = Reward::factory()->create([
        'family_id' => $family->id,
        'price_points' => 5,
    ]);

    $redemption = Redemption::factory()->create([
        'family_id' => $family->id,
        'wallet_id' => $wallet->id,
        'status' => 'pending',
        'requested_at' => now(),
    ]);

    $redemption->rewards()->attach($reward->id, [
        'quantity' => 1,
        'unit_price' => $reward->price_points,
        'total_price' => $reward->price_points,
    ]);

    // Assertions: relationships and integrity
    expect($family->users)->toHaveCount(2)
        ->and($wallet->family->is($family))->toBeTrue()
        ->and($wallet->user->is($child))->toBeTrue()
        ->and($chore->family->is($family))->toBeTrue()
        ->and($assignment->family->is($family))->toBeTrue()
        ->and($assignment->chore->is($chore))->toBeTrue()
        ->and($occurrence->family->is($family))->toBeTrue()
        ->and($occurrence->assignment->is($assignment))->toBeTrue()
        ->and($submission->occurrence->is($occurrence))->toBeTrue()
        ->and($submission->user->is($child))->toBeTrue()
        ->and($reward->family->is($family))->toBeTrue()
        ->and($redemption->family->is($family))->toBeTrue()
        ->and($redemption->wallet->is($wallet))->toBeTrue()
        ->and($redemption->rewards()->first()->is($reward))->toBeTrue();
});
