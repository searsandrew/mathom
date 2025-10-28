<?php

use App\Models\User;

uses(Tests\TestCase::class);

it('computes initials for multi-word names', function () {
    $u = User::factory()->make(['name' => 'Ada Lovelace']);
    expect($u->initials())->toBe('AL');
});

it('computes initials for single-word names', function () {
    $u = User::factory()->make(['name' => 'Prince']);
    expect($u->initials())->toBe('P');
});

it('trims extra spaces when computing initials', function () {
    $u = User::factory()->make(['name' => '  Jean   Luc   Picard  ']);
    expect($u->initials())->toBe('JLP');
});
