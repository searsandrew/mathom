<?php

use App\Models\Badge;
use App\Models\Family;
use App\Models\User;
use Illuminate\Support\Carbon;

it('creates badges with family and can be awarded to users', function () {
    $family = Family::factory()->create();
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Attach users to family (so they conceptually belong to same context)
    $family->users()->attach([$userA->id => ['role' => 'child', 'is_admin' => false]]);
    $family->users()->attach([$userB->id => ['role' => 'child', 'is_admin' => false]]);

    $badge = Badge::factory()->create(['family_id' => $family->id]);

    $now = now();
    $badge->users()->attach($userA->id, ['awarded_at' => $now]);
    $badge->users()->attach($userB->id, ['awarded_at' => $now->copy()->subDay()]);

    $awardees = $badge->users()->orderBy('users.id')->get();

    expect($badge->family->is($family))->toBeTrue()
        ->and($awardees)->toHaveCount(2)
        ->and($awardees->first()->pivot->awarded_at)->not->toBeNull();
});

it('scopes badges by family and casts criteria to array', function () {
    $family = Family::factory()->create();
    $otherFamily = Family::factory()->create();

    $b1 = Badge::factory()->create(['family_id' => $family->id]);
    $b2 = Badge::factory()->create(['family_id' => $family->id]);
    Badge::factory()->create(['family_id' => $otherFamily->id]);

    $list = Badge::forFamily($family->id)->pluck('id')->all();

    expect($list)->toEqualCanonicalizing([$b1->id, $b2->id])
        ->and(is_array($b1->criteria))->toBeTrue();
});

it('factory helper forFamily keeps the family as specified', function () {
    $family = Family::factory()->create();

    $badge = Badge::factory()->forFamily($family->id)->create();

    expect($badge->family_id)->toBe($family->id)
        ->and($badge->slug)->toBeString();
});
