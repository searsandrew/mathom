<?php

use App\Models\Category;
use App\Models\Chore;
use App\Models\Family;
use App\Models\Reward;

uses(Illuminate\Foundation\Testing\RefreshDatabase::class);

it('category relates to family, chores, and rewards', function () {
    $family = Family::factory()->create();

    $category = Category::factory()->forFamily($family->id)->create(['applies_to' => 'both']);

    $choreA = Chore::factory()->create(['family_id' => $family->id]);
    $choreB = Chore::factory()->create(['family_id' => $family->id]);

    $rewardA = Reward::factory()->create(['family_id' => $family->id]);
    $rewardB = Reward::factory()->create(['family_id' => $family->id]);

    $category->chores()->attach([$choreA->id, $choreB->id]);
    $category->rewards()->attach([$rewardA->id, $rewardB->id]);

    $category->load('family', 'chores', 'rewards');

    expect($category->family->is($family))->toBeTrue()
        ->and($category->chores)->toHaveCount(2)
        ->and($category->rewards)->toHaveCount(2);
});
