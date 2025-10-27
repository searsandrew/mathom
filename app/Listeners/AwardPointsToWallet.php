<?php

namespace App\Listeners;

use App\Events\SubmissionApproved;
use App\Services\LedgerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AwardPointsToWallet implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct(
        private LedgerService $ledger
    ){}

    /**
     * Handle the event.
     */
    public function handle(SubmissionApproved $event): void
    {
        $this->ledger->earnForOccurance(
            submission: $event->submission,
            occurence: $event->occurence,
            user: $event->user,
            points: $event->pointsAwarded,
        );
    }
}
