<?php

declare(strict_types=1);

use App\Console\Commands\SetupProject;

describe(SetupProject::class, function (): void {
    test('command exists and has correct signature', function (): void {
        $this->artisan('list')
            ->expectsOutputToContain('app:setup')
            ->assertSuccessful();
    });

    test('command description is accurate', function (): void {
        $exitCode = $this->artisan('app:setup', ['--help' => true]);

        $exitCode
            ->expectsOutputToContain('Initialize database, roles, permissions and create admin user')
            ->expectsOutputToContain('--admin-firstname')
            ->expectsOutputToContain('--admin-lastname')
            ->expectsOutputToContain('--admin-username')
            ->expectsOutputToContain('--admin-password')
            ->assertSuccessful();
    });
});
