<?php

use App\Events\BadgeAwarded;
use App\Models\Badge;
use App\Models\Family;
use App\Models\Ledger;
use App\Models\User;
use App\Models\Wallet;
use App\Services\BadgeEvaluator;
use Illuminate\Support\Facades\Event;

it('awards a badge when lifetime points threshold met and is idempotent', function () {
    Event::fake();

    $family = Family::factory()->create();
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['family_id' => $family->id, 'user_id' => $user->id, 'balance' => 0]);

    // Badge requires >= 100 lifetime points
    $badge = Badge::factory()->create([
        'family_id' => $family->id,
        'criteria' => ['lifetime_points' => ['gte' => 100]],
    ]);

    // Seed ledger with total 120 points on this wallet
    Ledger::create([
        'family_id' => $family->id,
        'wallet_id' => $wallet->id,
        'occurred_at' => now(),
        'type' => 'earn',
        'amount' => 70,
        'reference_type' => 'test',
        'reference_id' => null,
        'metadata' => [],
    ]);
    Ledger::create([
        'family_id' => $family->id,
        'wallet_id' => $wallet->id,
        'occurred_at' => now(),
        'type' => 'bonus',
        'amount' => 50,
        'reference_type' => 'test',
        'reference_id' => null,
        'metadata' => [],
    ]);

    $svc = new BadgeEvaluator();
    $svc->afterAllowancePaid($user);

    // assert awarded once
    expect($user->badges()->whereKey($badge->id)->exists())->toBeTrue();
    Event::assertDispatched(BadgeAwarded::class, 1);

    // calling again should be idempotent (no duplicate attachments)
    $svc->afterAllowancePaid($user);
    expect($user->badges()->whereKey($badge->id)->count())->toBe(1);
    Event::assertDispatched(BadgeAwarded::class, 1);
});

it('does not award badge when threshold not met', function () {
    Event::fake();

    $family = Family::factory()->create();
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['family_id' => $family->id, 'user_id' => $user->id, 'balance' => 0]);

    $badge = Badge::factory()->create([
        'family_id' => $family->id,
        'criteria' => ['lifetime_points' => ['gte' => 500]],
    ]);

    // Seed only 100 points
    Ledger::create([
        'family_id' => $family->id,
        'wallet_id' => $wallet->id,
        'occurred_at' => now(),
        'type' => 'earn',
        'amount' => 100,
        'reference_type' => 'test',
        'reference_id' => null,
        'metadata' => [],
    ]);

    (new BadgeEvaluator())->afterSubmissionApproved($user);

    expect($user->badges()->whereKey($badge->id)->exists())->toBeFalse();
    Event::assertNotDispatched(BadgeAwarded::class);
});
