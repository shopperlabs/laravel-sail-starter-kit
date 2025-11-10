<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Sleep;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function (): void {
        Str::createRandomStringsNormally();
        Str::createUuidsNormally();
        Http::preventStrayRequests();
        Process::preventStrayProcesses();
        Sleep::fake();

        $this->freezeTime();
    })
    ->in('Browser', 'Feature', 'Unit');

expect()->extend('toBeOne', fn () => $this->toBe(1));

function setupPermissionForResource(string $model, User $user): void
{
    $permissions = [
        "Create:$model",
        "View:$model",
        "ViewAny:$model",
        "Update:$model",
        "Delete:$model",
        "DeleteAny:$model",
        "RestoreAny:$model",
        "Restore:$model",
        "Replicate:$model",
        "ForceDelete:$model",
        "ForceDeleteAny:$model",
    ];

    foreach ($permissions as $permission) {
        Permission::create(['name' => $permission]);
    }

    Role::create(['name' => 'super_admin']);

    $user->assignRole(['super_admin']);
    $user->givePermissionTo($permissions);
}
