<?php

use App\Events\AllowanceDue;
use App\Models\Allowance;
use App\Models\AllowanceRun;
use App\Models\Wallet;
use App\Services\ProcessAllowance;
use Illuminate\Support\Carbon;

it('pays fixed allowance, records run and ledger, and updates wallet', function () {
    $wallet = Wallet::factory()->create(['balance' => 0]);
    $allowance = Allowance::factory()->forFamily($wallet->family_id)->create([
        'wallet_id' => $wallet->id,
        'mode' => 'fixed',
        'fixed_points' => 25,
    ]);

    $start = Carbon::parse('2025-10-20')->startOfDay()->toImmutable();
    $end = Carbon::parse('2025-10-27')->endOfDay()->toImmutable();

    $event = new AllowanceDue($allowance, $start, $end);

    (new ProcessAllowance())->handle($event);

    $run = AllowanceRun::where('allowance_id', $allowance->id)
        ->whereDate('started_at', $start->toDateString())
        ->whereDate('ended_at', $end->toDateString())
        ->first();

    expect($run)->not()->toBeNull()
        ->and($run->status)->toBe('paid')
        ->and($run->calc_summary)->toMatchArray(['mode' => 'fixed', 'points' => 25]);

    $wallet->refresh();

    expect((int)$wallet->balance)->toBe(25);
});

it('is idempotent for a period and does not double pay', function () {
    $wallet = Wallet::factory()->create(['balance' => 0]);
    $allowance = Allowance::factory()->forFamily($wallet->family_id)->create([
        'wallet_id' => $wallet->id,
        'mode' => 'fixed',
        'fixed_points' => 10,
    ]);

    $start = Carbon::parse('2025-10-01')->startOfDay()->toImmutable();
    $end = Carbon::parse('2025-10-07')->endOfDay()->toImmutable();

    $event = new AllowanceDue($allowance, $start, $end);

    $svc = new ProcessAllowance();
    $svc->handle($event);
    $svc->handle($event); // second time should no-op because run status is already "paid"

    $wallet->refresh();

    expect((int)$wallet->balance)->toBe(10)
        ->and(AllowanceRun::where('allowance_id', $allowance->id)->count())->toBe(1);
});
