<?php

use App\Models\Category;
use App\Models\Family;
use App\Models\Redemption;
use App\Models\Reward;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('reward relates to family, categories, and redemptions with pivot fields', function () {
    $family = Family::factory()->create();

    $reward = Reward::factory()->create(['family_id' => $family->id, 'price_points' => 25]);
    $catA = Category::factory()->forFamily($family->id)->create();
    $catB = Category::factory()->forFamily($family->id)->create();

    $reward->categories()->attach([$catA->id, $catB->id]);

    $redemption = Redemption::factory()->create([
        'family_id' => $family->id,
        'wallet_id' => \App\Models\Wallet::factory()->create(['family_id' => $family->id])->id,
        'status' => 'pending',
        'requested_at' => now(),
    ]);

    $reward->redemptions()->attach($redemption->id, [
        'quantity' => 3,
        'unit_price' => 25,
        'total_price' => 75,
    ]);

    $reward->load('family', 'categories', 'redemptions');

    $pivot = $reward->redemptions->first()->pivot;

    expect($reward->family->is($family))->toBeTrue()
        ->and($reward->categories)->toHaveCount(2)
        ->and($reward->redemptions)->toHaveCount(1)
        ->and((int) $pivot->quantity)->toBe(3)
        ->and((int) $pivot->unit_price)->toBe(25)
        ->and((int) $pivot->total_price)->toBe(75);
});
