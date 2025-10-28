<?php

use App\Events\AllowanceDue;
use App\Events\BadgeAwarded;
use App\Events\RedemptionCancelled;
use App\Events\RedemptionFulfilled;
use App\Events\RedemptionPlaced;
use App\Events\SubmissionApproved;
use App\Events\SubmissionRejected;
use App\Models\Allowance;
use App\Models\Badge;
use App\Models\Occurrence;
use App\Models\Redemption;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Support\Carbon;

it('constructs events and returns a PrivateChannel from broadcastOn', function () {
    $allowance = new Allowance();
    $start = Carbon::parse('2025-10-01')->startOfDay()->toImmutable();
    $end = Carbon::parse('2025-10-07')->endOfDay()->toImmutable();
    $e1 = new AllowanceDue($allowance, $start, $end);
    expect($e1->allowance->is($allowance))->toBeTrue()
        ->and($e1->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);

    $badge = new Badge();
    $e2 = new BadgeAwarded($badge);
    expect($e2->badge->is($badge))->toBeTrue()
        ->and($e2->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);

    $user = new User();
    $occ = new Occurrence();
    $sub = new Submission();
    $e3 = new SubmissionApproved($sub, $occ, $user, 10);
    expect($e3->submission->is($sub))->toBeTrue()
        ->and($e3->occurrence->is($occ))->toBeTrue()
        ->and($e3->user->is($user))->toBeTrue()
        ->and($e3->pointsAwarded)->toBe(10)
        ->and($e3->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);

    $e4 = new SubmissionRejected($sub, $occ, $user, 'reason');
    expect($e4->reason)->toBe('reason')
        ->and($e4->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);

    $redemption = new Redemption();
    $e5 = new RedemptionCancelled($redemption, 'oops');
    expect($e5->redemption->is($redemption))->toBeTrue()
        ->and($e5->reason)->toBe('oops')
        ->and($e5->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);

    $e6 = new RedemptionPlaced($redemption);
    $e7 = new RedemptionFulfilled($redemption);
    expect($e6->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class)
        ->and($e7->broadcastOn()[0])->toBeInstanceOf(PrivateChannel::class);
});
