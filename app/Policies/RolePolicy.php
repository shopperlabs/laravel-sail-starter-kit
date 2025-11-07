<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

final class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('ViewAny:Role');
    }

    public function view(User $user): bool
    {
        return $user->can('View:Role');
    }

    public function create(User $user): bool
    {
        return $user->can('Create:Role');
    }

    public function update(User $user): bool
    {
        return $user->can('Update:Role');
    }

    public function delete(User $user): bool
    {
        return $user->can('Delete:Role');
    }

    public function restore(User $user): bool
    {
        return $user->can('Restore:Role');
    }

    public function forceDelete(User $user): bool
    {
        return $user->can('ForceDelete:Role');
    }

    public function forceDeleteAny(User $user): bool
    {
        return $user->can('ForceDeleteAny:Role');
    }

    public function restoreAny(User $user): bool
    {
        return $user->can('RestoreAny:Role');
    }

    public function replicate(User $user): bool
    {
        return $user->can('Replicate:Role');
    }

    public function reorder(User $user): bool
    {
        return $user->can('Reorder:Role');
    }
}
