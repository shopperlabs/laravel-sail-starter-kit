<?php

declare(strict_types=1);

use App\Models\User;
use App\Policies\UserPolicy;
use Spatie\Permission\Models\Permission;

beforeEach(function (): void {
    $this->policy = new UserPolicy;
});

test('viewAny returns true when user has permission', function (): void {
    Permission::create(['name' => 'ViewAny:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('ViewAny:User');

    expect($this->policy->viewAny($user))->toBeTrue();
});

test('viewAny returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->viewAny($user))->toBeFalse();
});

test('view returns true when user has permission', function (): void {
    Permission::create(['name' => 'View:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('View:User');

    expect($this->policy->view($user))->toBeTrue();
});

test('view returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->view($user))->toBeFalse();
});

test('create returns true when user has permission', function (): void {
    Permission::create(['name' => 'Create:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('Create:User');

    expect($this->policy->create($user))->toBeTrue();
});

test('create returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->create($user))->toBeFalse();
});

test('update returns true when user has permission', function (): void {
    Permission::create(['name' => 'Update:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('Update:User');

    expect($this->policy->update($user))->toBeTrue();
});

test('update returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->update($user))->toBeFalse();
});

test('delete returns true when user has permission', function (): void {
    Permission::create(['name' => 'Delete:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('Delete:User');

    expect($this->policy->delete($user))->toBeTrue();
});

test('delete returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->delete($user))->toBeFalse();
});

test('restore returns true when user has permission', function (): void {
    Permission::create(['name' => 'Restore:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('Restore:User');

    expect($this->policy->restore($user))->toBeTrue();
});

test('restore returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->restore($user))->toBeFalse();
});

test('forceDelete returns true when user has permission', function (): void {
    Permission::create(['name' => 'ForceDelete:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('ForceDelete:User');

    expect($this->policy->forceDelete($user))->toBeTrue();
});

test('forceDelete returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->forceDelete($user))->toBeFalse();
});

test('forceDeleteAny returns true when user has permission', function (): void {
    Permission::create(['name' => 'ForceDeleteAny:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('ForceDeleteAny:User');

    expect($this->policy->forceDeleteAny($user))->toBeTrue();
});

test('forceDeleteAny returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->forceDeleteAny($user))->toBeFalse();
});

test('restoreAny returns true when user has permission', function (): void {
    Permission::create(['name' => 'RestoreAny:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('RestoreAny:User');

    expect($this->policy->restoreAny($user))->toBeTrue();
});

test('restoreAny returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->restoreAny($user))->toBeFalse();
});

test('replicate returns true when user has permission', function (): void {
    Permission::create(['name' => 'Replicate:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('Replicate:User');

    expect($this->policy->replicate($user))->toBeTrue();
});

test('replicate returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->replicate($user))->toBeFalse();
});

test('reorder returns true when user has permission', function (): void {
    Permission::create(['name' => 'Reorder:User']);
    $user = User::factory()->create();
    $user->givePermissionTo('Reorder:User');

    expect($this->policy->reorder($user))->toBeTrue();
});

test('reorder returns false when user does not have permission', function (): void {
    $user = User::factory()->create();

    expect($this->policy->reorder($user))->toBeFalse();
});
