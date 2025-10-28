<?php

namespace App\Services;

use App\Events\SubmissionApproved;
use App\Events\SubmissionRejected;
use App\Models\Submission;
use http\Exception\InvalidArgumentException;
use Illuminate\Support\Facades\DB;

class ChoreModerationService
{
    /**
     * @throws \Throwable
     */
    public function approve(Submission $submission, int $points): void
    {
        DB::transaction(function () use ($submission, $points) {
            $submission->load(['occurrence.assignment.assignee', 'occurrence.assignment.chore']);

            if($submission->status === 'approved') return;

            if($submission->status !== 'pending') {
                throw new InvalidArgumentException('Only pending submissions can be approved.');
            }

            $occ = $submission->occurrence;
            $used = $occ->assignment->assignee;

            $submission->update(['status' => 'approved']);
            $occ->update(['status' => 'approved']);

            SubmissionApproved::dispatch($submission, $occ, $used, $points);
        });
    }

    /**
     * @throws \Throwable
     */
    public function reject(Submission $submission, ?string $reason = null): void
    {
        DB::transaction(function () use ($submission, $reason) {
            $submission->load('occurrence.assignment.assignee');

            if ($submission->status === 'rejected') return;
            if($submission->status !== 'pending') {
                throw new InvalidArgumentException('Only pending submissions can be rejected.');
            }

            $occ = $submission->occurrence;
            $occ->update(['status' => 'rejected']);
            $submission->update(['status' => 'rejected']);

            SubmissionRejected::dispatch($submission, $occ, $occ->assignment->assignee, $reason);
        });
    }
}
