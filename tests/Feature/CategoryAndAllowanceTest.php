<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\Family;
use App\Models\Reward;
use App\Models\Wallet;
use App\Models\Allowance;

it('creates categories with family and can attach to chores and rewards', function () {
    $family = Family::factory()->create();

    $chore = Chore::factory()->create(['family_id' => $family->id]);
    $reward = Reward::factory()->create(['family_id' => $family->id]);

    $category = Category::factory()->create(['family_id' => $family->id, 'applies_to' => 'both']);

    // Attach
    $chore->categories()->attach($category->id);
    $reward->categories()->attach($category->id);

    expect($category->family->is($family))->toBeTrue()
        ->and($chore->categories()->count())->toBe(1)
        ->and($reward->categories()->count())->toBe(1);
});

it('filters categories by applies_to scope and by family scope', function () {
    $family = Family::factory()->create();
    $otherFamily = Family::factory()->create();

    $c1 = Category::factory()->create(['family_id' => $family->id, 'applies_to' => 'chores']);
    $c2 = Category::factory()->create(['family_id' => $family->id, 'applies_to' => 'rewards']);
    $c3 = Category::factory()->create(['family_id' => $family->id, 'applies_to' => 'both']);
    Category::factory()->create(['family_id' => $otherFamily->id, 'applies_to' => 'both']);

    $forChores = Category::forFamily($family->id)->appliesTo('chores')->get();
    $forRewards = Category::forFamily($family->id)->appliesTo('rewards')->get();

    expect($forChores->pluck('id')->all())->toEqualCanonicalizing([$c1->id, $c3->id])
        ->and($forRewards->pluck('id')->all())->toEqualCanonicalizing([$c2->id, $c3->id]);
});

it('creates allowances with coherent family and wallet, with casts and scopes working', function () {
    $family = Family::factory()->create();
    $wallet = Wallet::factory()->create(['family_id' => $family->id]);

    // Explicit family and wallet
    $allowance = Allowance::factory()->create([
        'family_id' => $family->id,
        'wallet_id' => $wallet->id,
        'status' => 'active',
        'starts_at' => now()->subDay(),
        'ends_at' => now()->addDay(),
        'fixed_points' => 10,
        'min_approved_occurrences' => 2,
        'bonus_points_on_threshold' => 5,
    ]);

    expect($allowance->family->is($family))->toBeTrue()
        ->and($allowance->wallet->is($wallet))->toBeTrue()
        ->and($allowance->fixed_points)->toBeInt()
        ->and($allowance->starts_at)->toBeInstanceOf(\Illuminate\Support\Carbon::class)
        ->and(Allowance::forFamily($family->id)->active()->pluck('id')->all())
            ->toContain($allowance->id);

    // Using factory default to auto-create wallet in same family
    $auto = Allowance::factory()->forFamily($family->id)->create();
    expect($auto->family_id)->toBe($family->id)
        ->and($auto->wallet->family_id)->toBe($family->id);
});
