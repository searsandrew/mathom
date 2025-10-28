<?php

use App\Models\Allowance;
use App\Models\AllowanceRun;
use Illuminate\Support\Carbon;

it('casts calc_summary to array and dates to Carbon and has no timestamps', function () {
    $allowance = Allowance::factory()->create();

    $run = AllowanceRun::create([
        'allowance_id' => $allowance->id,
        'started_at' => '2025-10-01 00:00:00',
        'ended_at' => '2025-10-07 23:59:59',
        'status' => 'calculated',
        'calc_summary' => ['mode' => 'fixed', 'points' => 5],
    ]);

    expect($run->calc_summary)->toBeArray()
        ->and($run->calc_summary['mode'])->toBe('fixed')
        ->and($run->started_at)->toBeInstanceOf(Carbon::class)
        ->and($run->ended_at)->toBeInstanceOf(Carbon::class);

    // Ensure timestamps are not auto-managed (model has no created_at/updated_at attributes)
    expect(array_key_exists('created_at', $run->getAttributes()))->toBeFalse()
        ->and(array_key_exists('updated_at', $run->getAttributes()))->toBeFalse();
});
