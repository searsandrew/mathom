<?php

use App\Services\BadgeEvaluator;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use App\Events\BadgeAwarded;

uses(Tests\TestCase::class);

it('does nothing when user has no family and no wallet (early return)', function () {
    Event::fake();

    $user = User::factory()->make(); // not persisted; no wallet, no family pivot

    (new BadgeEvaluator())->afterRedemptionFulfilled($user);

    Event::assertNotDispatched(BadgeAwarded::class);
});
