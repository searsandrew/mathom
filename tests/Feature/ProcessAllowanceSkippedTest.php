<?php

use App\Events\AllowanceDue;
use App\Models\Allowance;
use App\Models\AllowanceRun;
use App\Models\Ledger;
use App\Models\Wallet;
use App\Services\ProcessAllowance;
use Illuminate\Support\Carbon;

it('marks run as skipped and does not create ledger when points are zero', function () {
    $wallet = Wallet::factory()->create(['balance' => 100]);
    $allowance = Allowance::factory()->forFamily($wallet->family_id)->create([
        'wallet_id' => $wallet->id,
        'mode' => 'fixed',
        'fixed_points' => 0,
    ]);

    $start = Carbon::parse('2025-10-13')->startOfDay()->toImmutable();
    $end = Carbon::parse('2025-10-19')->endOfDay()->toImmutable();

    $event = new AllowanceDue($allowance, $start, $end);

    (new ProcessAllowance())->handle($event);

    $run = AllowanceRun::where('allowance_id', $allowance->id)
        ->whereDate('started_at', $start->toDateString())
        ->whereDate('ended_at', $end->toDateString())
        ->first();

    expect($run)->not()->toBeNull()
        ->and($run->status)->toBe('skipped')
        ->and($run->calc_summary)->toMatchArray(['mode' => 'fixed', 'points' => 0]);

    expect(Ledger::where('reference_type', 'allowance_run')->where('reference_id', $run->id)->exists())->toBeFalse();

    $wallet->refresh();
    expect((int)$wallet->balance)->toBe(100);
});
