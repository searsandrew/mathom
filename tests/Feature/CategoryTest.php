<?php

use App\Models\Category;
use App\Models\Family;

it('filters categories by family and appliesTo scope', function () {
    $famA = Family::factory()->create();
    $famB = Family::factory()->create();

    $c1 = Category::factory()->forFamily($famA->id)->create(['applies_to' => 'chores']);
    $c2 = Category::factory()->forFamily($famA->id)->create(['applies_to' => 'rewards']);
    $c3 = Category::factory()->forFamily($famA->id)->create(['applies_to' => 'both']);

    $other = Category::factory()->forFamily($famB->id)->create(['applies_to' => 'both']);

    $forFamA = Category::forFamily($famA->id)->pluck('id')->all();
    expect($forFamA)->toEqualCanonicalizing([$c1->id, $c2->id, $c3->id]);

    $forChores = Category::forFamily($famA->id)->appliesTo('chores')->pluck('id')->all();
    expect($forChores)->toEqualCanonicalizing([$c1->id, $c3->id]);

    $forRewards = Category::forFamily($famA->id)->appliesTo('rewards')->pluck('id')->all();
    expect($forRewards)->toEqualCanonicalizing([$c2->id, $c3->id]);
});
