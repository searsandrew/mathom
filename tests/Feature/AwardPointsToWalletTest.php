<?php

use App\Controllers; // noop to avoid empty file warning in some editors
use App\Events\SubmissionApproved;
use App\Listeners\AwardPointsToWallet;
use App\Models\Assignment;
use App\Models\Occurrence;
use App\Models\Submission;
use App\Models\User;
use App\Services\LedgerService;
use Illuminate\Support\Carbon;

it('calls LedgerService::earnForOccurrence when SubmissionApproved is handled', function () {
    $user = User::factory()->create(['name' => 'Ada Lovelace']);
    // Ensure the user has a wallet
    $user->wallet()->create(['family_id' => \App\Models\Family::factory()->create()->id, 'balance' => 0]);

    $assignment = Assignment::factory()->forUser($user)->create([
        'family_id' => $user->wallet->family_id,
    ]);

    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'due_date' => Carbon::parse('2025-10-27'),
        'status' => 'pending',
    ]);

    $submission = Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $user->id,
        'status' => 'pending',
    ]);

    $mock = Mockery::mock(LedgerService::class);
    $mock->shouldReceive('earnForOccurrence')
        ->once()
        ->with(
            Mockery::on(fn($sub) => $sub->is($submission)),
            Mockery::on(fn($occ) => $occ->is($occurrence)),
            Mockery::on(fn($u) => $u->is($user)),
            15
        );

    app()->instance(LedgerService::class, $mock);

    $listener = app(AwardPointsToWallet::class);
    $listener->handle(new SubmissionApproved($submission, $occurrence, $user, 15));
});
