<?php

declare(strict_types=1);

use App\Models\User;
use Filament\Panel;
use Spatie\Permission\Models\Role;

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

test('can access panel when user has valid email domain, is verified and is admin', function (): void {
    Role::create(['name' => 'super_admin']);

    /** @var string $domain */
    $domain = config('app.domain');

    $user = User::factory()->create([
        'email' => "admin@{$domain}",
        'email_verified_at' => now(),
    ]);

    $user->assignRole('super_admin');

    $panel = Mockery::mock(Panel::class);

    expect($user->canAccessPanel($panel))->toBeTrue();
});

test('cannot access panel when email domain does not match', function (): void {
    Role::create(['name' => 'super_admin']);

    $user = User::factory()->create([
        'email' => 'admin@wrongdomain.com',
        'email_verified_at' => now(),
    ]);

    $user->assignRole('super_admin');

    $panel = Mockery::mock(Panel::class);

    expect($user->canAccessPanel($panel))->toBeFalse();
});

test('cannot access panel when email is not verified', function (): void {
    Role::create(['name' => 'super_admin']);

    /** @var string $domain */
    $domain = config('app.domain');

    $user = User::factory()->create([
        'email' => "admin@{$domain}",
        'email_verified_at' => null,
    ]);

    $user->assignRole('super_admin');

    $panel = Mockery::mock(Panel::class);

    expect($user->canAccessPanel($panel))->toBeFalse();
});

test('cannot access panel when user is not admin', function (): void {
    /** @var string $domain */
    $domain = config('app.domain');

    $user = User::factory()->create([
        'email' => "user@{$domain}",
        'email_verified_at' => now(),
    ]);

    $panel = Mockery::mock(Panel::class);

    expect($user->canAccessPanel($panel))->toBeFalse();
});

test('is admin returns true when user has super admin role', function (): void {
    Role::create(['name' => 'super_admin']);

    $user = User::factory()->create();
    $user->assignRole('super_admin');

    expect($user->isAdmin())->toBeTrue();
});

test('is admin returns true when user has panel user role', function (): void {
    Role::create(['name' => 'panel_user']);

    $user = User::factory()->create();
    $user->assignRole('panel_user');

    expect($user->isAdmin())->toBeTrue();
});

test('is admin returns false when user has no admin role', function (): void {
    $user = User::factory()->create();

    expect($user->isAdmin())->toBeFalse();
});

test('get filament name returns full name', function (): void {
    $user = User::factory()->create([
        'firstname' => 'John',
        'lastname' => 'Doe',
    ]);

    expect($user->getFilamentName())->toBe('John Doe');
});

test('name accessor returns full name', function (): void {
    $user = User::factory()->create([
        'firstname' => 'Jane',
        'lastname' => 'Smith',
    ]);

    expect($user->name)->toBe('Jane Smith');
});
