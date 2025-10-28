<?php

use App\Models\Allowance;
use App\Models\AllowanceRun;
use App\Models\Ledger;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

it('loads allowance and ledger relations on AllowanceRun', function () {
    $allowance = Allowance::factory()->create();

    $run = AllowanceRun::create([
        'allowance_id' => $allowance->id,
        'started_at' => now()->subWeek(),
        'ended_at' => now(),
        'status' => 'calculated',
        'calc_summary' => ['mode' => 'fixed', 'points' => 1],
    ]);

    $ledger = Ledger::create([
        'family_id' => $allowance->family_id,
        'wallet_id' => $allowance->wallet_id,
        'occurred_at' => now(),
        'type' => 'allowance_payout',
        'amount' => 1,
        'reference_type' => 'allowance_run',
        'reference_id' => $run->id,
        'metadata' => [],
    ]);

    $run->update(['ledger_id' => $ledger->id]);

    expect($run->allowance)->not()->toBeNull()
        ->and($run->allowance->is($allowance))->toBeTrue()
        ->and($run->ledger)->not()->toBeNull()
        ->and($run->ledger->is($ledger))->toBeTrue();
});
