<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

final class SetupProject extends Command
{
    protected $signature = 'app:setup
                            {--admin-firstname= : Admin user first name}
                            {--admin-lastname= : Admin user last name}
                            {--admin-username= : Admin username (used for email generation)}
                            {--admin-password= : Admin user password}';

    protected $description = 'Initialize database, roles, permissions and create admin user';

    public function handle(): void
    {
        $this->info('🚀 Starting App Initialization...');

        $this->runMigrations()
            ->generateRolesAndPermissions()
            ->storageLink()
            ->createAdminUser()
            ->displayResult();
    }

    private function runMigrations(): self
    {
        $this->info('⚙️ Running database migrations...');

        Artisan::call('migrate', ['--force' => true], $this->getOutput());

        $this->info('✅ Migrations completed successfully.');

        return $this;
    }

    private function generateRolesAndPermissions(): self
    {
        $this->info('🛡 Generating roles and permissions...');

        Artisan::call('shield:generate', [
            '--panel' => 'admin',
            '--ignore-existing-policies' => true,
            '--all' => true,
        ], $this->getOutput());

        $this->info('✅ Roles and permissions generated and assigned successfully.');

        return $this;
    }

    private function storageLink(): self
    {
        if (file_exists(public_path('storage'))) {
            return $this;
        }

        $this->info('🔗 Linking storage directory...');

        Artisan::call('storage:link', [], $this->getOutput());

        $this->info('✅ Storage directory linked successfully.');

        return $this;
    }

    private function createAdminUser(): self
    {
        $this->newLine();

        Artisan::call('app:create-admin', [
            '--firstname' => $this->option('admin-firstname'),
            '--lastname' => $this->option('admin-lastname'),
            '--username' => $this->option('admin-username'),
            '--password' => $this->option('admin-password'),
        ], $this->getOutput());

        return $this;
    }

    private function displayResult(): void
    {
        $this->newLine();

        /** @var string $url */
        $url = config('app.url');

        $this->info($this->logo());
        $this->info('🔥 Your project has been initialized properly.');

        if ($this->isLocalEnvironment()) {
            $this->info('You can now run it with browsing '.$url);
            $this->info('You can customize your environment files.');

            $this->newLine(2);

            $this->info('✦ Happy coding! 🚀🚀🚀 :: We Must Ship ✦');
        } else {
            $this->info('Production/Staging environment ready! 🚀');

            $this->info('Application URL: '.$url);
        }
    }

    private function logo(): string
    {
        return
            <<<'HEADER'
         █████╗  ██████╗  ██████╗
        ██╔══██╗ ██╔══██╗ ██╔══██╗
        ███████║ ██████╔╝ ██████╔╝
        ██╔══██║ ██╔═══╝  ██╔═══╝
        ██║  ██║ ██║      ██║
        ╚═╝  ╚═╝ ╚═╝      ╚═╝
        HEADER;
    }

    private function isLocalEnvironment(): bool
    {
        return app()->isLocal();
    }
}
