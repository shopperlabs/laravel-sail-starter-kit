<?php

declare(strict_types=1);

use App\Filament\Resources\Users\Pages\ManageUsers;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use Filament\Actions\CreateAction;
use Filament\Actions\Testing\TestAction;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;

/**
 * @var Tests\TestCase $this
 */
beforeEach(function (): void {
    /** @var string $domain */
    $domain = config('app.domain');

    $this->admin = User::factory()->create([
        'email' => "admin@{$domain}",
        'email_verified_at' => now(),
    ]);

    setupPermissionForResource('User', $this->admin);

    $this->actingAs($this->admin);
});

test('has correct navigation group', function (): void {
    expect(UserResource::getNavigationGroup())
        ->toBe(__('filament-shield::filament-shield.nav.group'));
});

test('can render user resource page', function (): void {
    Livewire::test(ManageUsers::class)
        ->assertSuccessful();
});

test('can list users with roles', function (): void {
    $userWithRole = User::factory()->create();
    $userWithRole->assignRole('super_admin');

    $userWithoutRole = User::factory()->create();

    Livewire::test(ManageUsers::class)
        ->assertCanSeeTableRecords([$this->admin, $userWithRole])
        ->assertCanNotSeeTableRecords([$userWithoutRole]);
});

test('can create user', function (): void {
    Livewire::test(ManageUsers::class)
        ->callAction(CreateAction::class, data: [
            'firstname' => 'John',
            'lastname' => 'Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
            'roles' => Role::query()->pluck('id')->toArray(),
        ])
        ->assertHasNoFormErrors();

    $this->assertDatabaseHas(User::class, [
        'firstname' => 'John',
        'lastname' => 'Doe',
        'email' => 'john@example.com',
    ]);
});

test('can edit user', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    Livewire::test(ManageUsers::class)
        ->callAction(TestAction::make('edit')->table($user), data: [
            'firstname' => 'Jane',
            'lastname' => 'Smith',
            'email' => $user->email,
        ])
        ->assertHasNoFormErrors();

    expect($user->refresh())
        ->firstname->toBe('Jane')
        ->lastname->toBe('Smith');
});

test('can delete user', function (): void {
    $user = User::factory()->create();
    $user->assignRole('super_admin');

    Livewire::test(ManageUsers::class)
        ->callAction(TestAction::make('delete')->table($user));

    $this->assertModelMissing($user);
});

test('cannot edit currently authenticated user', function (): void {
    Livewire::test(ManageUsers::class)
        ->assertActionHidden(TestAction::make('edit')->table($this->admin));
});

test('cannot delete currently authenticated user', function (): void {
    Livewire::test(ManageUsers::class)
        ->assertActionHidden(TestAction::make('delete')->table($this->admin));
});

test('table displays correct columns', function (): void {
    Livewire::test(ManageUsers::class)
        ->assertCanSeeTableRecords([$this->admin])
        ->assertCanRenderTableColumn('firstname')
        ->assertCanRenderTableColumn('lastname')
        ->assertCanRenderTableColumn('email')
        ->assertCanRenderTableColumn('roles.name');
});
