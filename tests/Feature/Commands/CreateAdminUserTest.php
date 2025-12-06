<?php

declare(strict_types=1);

use App\Console\Commands\CreateAdminUser;
use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\Models\Role;

use function Pest\Laravel\artisan;
use function Pest\Laravel\assertDatabaseHas;

beforeEach(function (): void {
    Role::query()->firstOrCreate(['name' => Utils::getSuperAdminName()]);
});

describe(CreateAdminUser::class, function (): void {
    test('can create admin user with all required options', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'john.doe',
            '--password' => 'password123',
        ])
            ->assertSuccessful();

        assertDatabaseHas('users', [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john.doe@laravel.local',
        ]);

        $admin = User::query()->where('email', 'john.doe@laravel.local')->first();

        expect($admin)
            ->not->toBeNull()
            ->and($admin->hasRole(Utils::getSuperAdminName()))
            ->toBeTrue()
            ->and($admin->email_verified_at)
            ->not->toBeNull();
    });

    test('cannot create admin with username containing uppercase letters', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'John.Doe',
            '--password' => 'password123',
        ])
            ->assertFailed();

        expect(User::query()->where('email', 'John.Doe@laravel.local')->exists())
            ->toBeFalse();
    });

    test('cannot create admin with username containing spaces', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'john doe',
            '--password' => 'password123',
        ])
            ->assertFailed();

        expect(User::query()->where('email', 'john doe@laravel.local')->exists())
            ->toBeFalse();
    });

    test('cannot create admin with username less than 3 characters', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'ab',
            '--password' => 'password123',
        ])
            ->assertFailed();

        expect(User::query()->where('email', 'ab@laravel.local')->exists())
            ->toBeFalse();
    });

    test('cannot create admin with password less than 8 characters', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'john.doe',
            '--password' => 'pass',
        ])
            ->assertFailed();

        expect(User::query()->where('email', 'john.doe@laravel.local')->exists())
            ->toBeFalse();
    });

    test('cannot create admin with duplicate email', function (): void {
        User::factory()->create([
            'email' => 'john.doe@laravel.local',
        ]);

        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'john.doe',
            '--password' => 'password123',
        ])
            ->assertFailed();

        expect(User::query()->where('email', 'john.doe@laravel.local')->count())
            ->toBe(1);
    });

    test('can create multiple admin users with different usernames', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'john.doe',
            '--password' => 'password123',
        ])
            ->assertSuccessful();

        artisan('app:create-admin', [
            '--firstname' => 'Jane',
            '--lastname' => 'Smith',
            '--username' => 'jane.smith',
            '--password' => 'password456',
        ])
            ->assertSuccessful();

        expect(User::query()->count())
            ->toBe(2)
            ->and(User::query()->where('email', 'john.doe@laravel.local')->exists())
            ->toBeTrue()
            ->and(User::query()->where('email', 'jane.smith@laravel.local')->exists())
            ->toBeTrue();
    });

    test('username can contain valid special characters', function (): void {
        artisan('app:create-admin', [
            '--firstname' => 'John',
            '--lastname' => 'Doe',
            '--username' => 'john.doe-123_test',
            '--password' => 'password123',
        ])
            ->assertSuccessful();

        assertDatabaseHas('users', [
            'email' => 'john.doe-123_test@laravel.local',
        ]);
    });
});
