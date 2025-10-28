<?php

use App\Models\Assignment;
use App\Models\Ledger;
use App\Models\Occurrence;
use App\Models\Redemption;
use App\Models\Reward;
use App\Models\User;
use App\Models\Wallet;
use App\Services\LedgerService;
use Illuminate\Support\Carbon;

it('earns for occurrence, updates wallet and occurrence, and is idempotent', function () {
    $user = User::factory()->create();
    $familyId = \App\Models\Family::factory()->create()->id;
    $wallet = Wallet::factory()->create(['family_id' => $familyId, 'user_id' => $user->id, 'balance' => 0]);

    $assignment = Assignment::factory()->forUser($user)->create([
        'family_id' => $familyId,
    ]);

    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $familyId,
        'due_date' => Carbon::parse('2025-10-20'),
        'status' => 'pending',
    ]);

    $submission = \App\Models\Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $svc = new LedgerService();

    $svc->earnForOccurrence($submission, $occurrence, $user, 12);
    $svc->earnForOccurrence($submission, $occurrence, $user, 12); // should no-op second time

    $wallet->refresh();
    $occurrence->refresh();

    expect((int)$wallet->balance)->toBe(12)
        ->and($occurrence->status)->toBe('approved')
        ->and((int)$occurrence->points_awarded)->toBe(12)
        ->and(Ledger::where('reference_type', 'occurrence')->where('reference_id', $occurrence->getKey())->where('type', 'earn')->count())->toBe(1);
});

it('handles redemption hold, release, and capture flows idempotently', function () {
    $user = User::factory()->create();
    $wallet = Wallet::factory()->create(['user_id' => $user->id, 'balance' => 1000]);

    $rewardA = Reward::factory()->create(['family_id' => $wallet->family_id, 'price_points' => 50]);
    $rewardB = Reward::factory()->create(['family_id' => $wallet->family_id, 'price_points' => 75]);

    $redemption = Redemption::factory()->create([
        'family_id' => $wallet->family_id,
        'wallet_id' => $wallet->id,
        'status' => 'pending',
        'requested_at' => now(),
        'approved_at' => null,
        'fulfilled_at' => null,
        'cancelled_at' => null,
    ]);

    // attach rewards with pivot totals
    $redemption->rewards()->attach($rewardA->id, ['quantity' => 2, 'unit_price' => 50, 'total_price' => 100]);
    $redemption->rewards()->attach($rewardB->id, ['quantity' => 1, 'unit_price' => 75, 'total_price' => 75]);

    $svc = new LedgerService();

    // place hold (total 175)
    $svc->placeHoldForRedemption($redemption);
    $svc->placeHoldForRedemption($redemption); // idempotent

    $wallet->refresh();
    expect((int) $wallet->balance)->toBe(825)
        ->and(Ledger::where('reference_type', 'redemption')->where('reference_id', $redemption->id)->where('type', 'redeem_hold')->count())->toBe(1);

    // release hold
    $svc->releaseHoldForRedemption($redemption, 'cancelled');
    $svc->releaseHoldForRedemption($redemption, 'cancelled'); // idempotent

    $wallet->refresh();
    expect((int) $wallet->balance)->toBe(1000)
        ->and(Ledger::where('reference_type', 'redemption')->where('reference_id', $redemption->id)->where('type', 'redeem_release')->count())->toBe(1);

    // capture (no balance change)
    $svc->captureHoldForRedemption($redemption);
    $svc->captureHoldForRedemption($redemption);

    expect(Ledger::where('reference_type', 'redemption')->where('reference_id', $redemption->id)->where('type', 'redeem_capture')->count())->toBe(1);
});
