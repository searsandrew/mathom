<?php

use App\Models\Family;
use App\Models\Ledger;
use App\Models\Wallet;

uses(Tests\TestCase::class, Illuminate\Foundation\Testing\RefreshDatabase::class);

it('casts metadata to array and relates to wallet and family', function () {
    $family = Family::factory()->create();
    $wallet = Wallet::factory()->create(['family_id' => $family->id]);

    $entry = Ledger::create([
        'family_id' => $family->id,
        'wallet_id' => $wallet->id,
        'occurred_at' => now(),
        'type' => 'manual_adjust',
        'amount' => 42,
        'reference_type' => 'test',
        'reference_id' => null,
        'metadata' => ['note' => 'hello'],
    ]);

    expect($entry->metadata)->toBeArray()
        ->and($entry->wallet->is($wallet))->toBeTrue()
        ->and($entry->family->is($family))->toBeTrue();
});

it('wallet relates to family, user and has many ledger entries', function () {
    $wallet = Wallet::factory()->create();

    $e1 = Ledger::create([
        'family_id' => $wallet->family_id,
        'wallet_id' => $wallet->id,
        'occurred_at' => now(),
        'type' => 'earn',
        'amount' => 10,
        'reference_type' => 'x',
        'reference_id' => null,
        'metadata' => [],
    ]);
    $e2 = Ledger::create([
        'family_id' => $wallet->family_id,
        'wallet_id' => $wallet->id,
        'occurred_at' => now(),
        'type' => 'bonus',
        'amount' => 5,
        'reference_type' => 'x',
        'reference_id' => null,
        'metadata' => [],
    ]);

    expect($wallet->family)->not()->toBeNull()
        ->and($wallet->user)->not()->toBeNull()
        ->and($wallet->ledger)->toHaveCount(2)
        ->and($wallet->ledger[0]->is($e1) || $wallet->ledger[1]->is($e1))->toBeTrue();
});
