<?php

declare(strict_types=1);

use App\Models\User;

test('to array', function (): void {
    $user = User::factory()->create()->refresh();

    expect(array_keys($user->toArray()))
        ->toBe([
            'id',
            'public_id',
            'firstname',
            'lastname',
            'email',
            'email_verified_at',
            'created_at',
            'updated_at',
        ]);
});

test('find by public id', function (): void {
    $user = User::factory()->create();

    $foundUser = User::findByPublicId($user->public_id);

    expect($foundUser)->not->toBeNull()
        ->and($foundUser->id)->toBe($user->id)
        ->and($foundUser->public_id)->toBe($user->public_id);
});

test('find by public id returns null when not found', function (): void {
    $foundUser = User::findByPublicId('non-existent-id');

    expect($foundUser)->toBeNull();
});

test('where public id scope', function (): void {
    $user = User::factory()->create();

    $foundUser = User::query()->wherePublicId($user->public_id)->first();

    expect($foundUser)->not->toBeNull()
        ->and($foundUser->id)->toBe($user->id);
});
