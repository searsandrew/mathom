<?php

use App\Events\SubmissionApproved;
use App\Events\SubmissionRejected;
use App\Models\Assignment;
use App\Models\Occurrence;
use App\Models\Submission;
use App\Models\User;
use App\Services\ChoreModerationService;
use Illuminate\Support\Facades\Event;

it('approves a pending submission, updates occurrence, and dispatches event', function () {
    Event::fake();

    $assignment = Assignment::factory()->forUser(User::factory()->create())->create();
    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'status' => 'pending',
    ]);
    $submission = Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $assignment->user_id,
        'status' => 'pending',
    ]);

    (new ChoreModerationService())->approve($submission, 15);

    $submission->refresh();
    $occurrence->refresh();

    expect($submission->status)->toBe('approved')
        ->and($occurrence->status)->toBe('approved');

    Event::assertDispatched(SubmissionApproved::class, function ($event) use ($submission, $occurrence) {
        return $event->submission->is($submission)
            && $event->occurrence->is($occurrence)
            && $event->pointsAwarded === 15;
    });
});

it('rejects a pending submission and dispatches event', function () {
    Event::fake();

    $assignment = Assignment::factory()->forUser(User::factory()->create())->create();
    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'status' => 'pending',
    ]);
    $submission = Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $assignment->user_id,
        'status' => 'pending',
    ]);

    (new ChoreModerationService())->reject($submission, 'not good enough');

    $submission->refresh();
    $occurrence->refresh();

    expect($submission->status)->toBe('rejected')
        ->and($occurrence->status)->toBe('rejected');

    Event::assertDispatched(SubmissionRejected::class, function ($event) use ($submission, $occurrence) {
        return $event->submission->is($submission)
            && $event->occurrence->is($occurrence)
            && $event->reason === 'not good enough';
    });
});

it('throws on approving non-pending submission', function () {
    $assignment = Assignment::factory()->forUser(User::factory()->create())->create();
    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'status' => 'approved',
    ]);
    $submission = Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $assignment->user_id,
        'status' => 'approved',
    ]);

    (new ChoreModerationService())->approve($submission, 5);
})->throws(\InvalidArgumentException::class);

it('throws on rejecting non-pending submission', function () {
    $assignment = Assignment::factory()->forUser(User::factory()->create())->create();
    $occurrence = Occurrence::factory()->create([
        'assignment_id' => $assignment->id,
        'family_id' => $assignment->family_id,
        'status' => 'approved',
    ]);
    $submission = Submission::factory()->create([
        'occurrence_id' => $occurrence->id,
        'user_id' => $assignment->user_id,
        'status' => 'approved',
    ]);

    (new ChoreModerationService())->reject($submission, 'nope');
})->throws(\InvalidArgumentException::class);
