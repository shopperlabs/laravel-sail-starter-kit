<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

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
        $this->info('👤 Creating an Admin user...');

        $credentials = $this->getAdminCredentials();

        /** @var User $admin */
        $admin = User::query()->create([
            'firstname' => $credentials['firstname'],
            'lastname' => $credentials['lastname'],
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($this->getAdminRoleName());

        $this->info("✅ Admin user '{$admin->name}' created and assigned the '{$this->getAdminRoleName()}' role successfully.");

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

    /**
     * @return array{firstname: string, lastname: string, email: string, password: string}
     */
    private function getAdminCredentials(): array
    {
        /** @var ?string $firstname */
        $firstname = $this->option('admin-firstname');
        /** @var string $domain */
        $domain = config('app.domain');

        if (blank($firstname)) {
            $firstname = text(
                label: 'First name',
                placeholder: 'John',
                required: true,
            );
        }

        /** @var ?string $lastname */
        $lastname = $this->option('admin-lastname');

        if (blank($lastname)) {
            $lastname = text(
                label: 'Last name',
                placeholder: 'Doe',
                required: true
            );
        }

        /** @var ?string $username */
        $username = $this->option('admin-username');

        if (blank($username)) {
            $username = text(
                label: 'Username (for email)',
                placeholder: 'john.doe',
                required: true,
                validate: fn (string $value): ?string => $this->validateUsername($value)
            );
        } else {
            $usernameValidation = $this->validateUsername($username);

            if ($usernameValidation) {
                $this->error("Invalid username: {$usernameValidation}");

                exit(1);
            }
        }

        $email = "{$username}@{$domain}";

        $emailValidation = $this->validateAdminEmail($email);

        if ($emailValidation) {
            $this->error("Invalid email: {$emailValidation}");

            exit(1);
        }

        /** @var ?string $passwordInput */
        $passwordInput = $this->option('admin-password');

        if (blank($passwordInput)) {
            $passwordInput = password(
                label: 'Password',
                required: true,
                validate: fn (string $value): ?string => $this->validateAdminPassword($value)
            );
        } else {
            $passwordValidation = $this->validateAdminPassword($passwordInput);

            if ($passwordValidation) {
                $this->error("Invalid password: {$passwordValidation}");

                exit(1);
            }
        }

        return [
            'firstname' => $firstname,
            'lastname' => $lastname,
            'email' => $email,
            'password' => Hash::make($passwordInput),
        ];
    }

    private function getAdminRoleName(): string
    {
        return Utils::getSuperAdminName();
    }

    private function validateUsername(string $username): ?string
    {
        if (preg_match('/[A-Z]/', $username)) {
            return 'Username must not contain uppercase letters.';
        }

        if (preg_match('/\s/', $username)) {
            return 'Username must not contain spaces.';
        }

        if (! preg_match('/^[a-z0-9._-]+$/', $username)) {
            return 'Username can only contain lowercase letters, numbers, dots, underscores and hyphens.';
        }

        if (mb_strlen($username) < 3) {
            return 'Username must be at least 3 characters long.';
        }

        return null;
    }

    private function validateAdminEmail(string $email): ?string
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 'Please enter a valid email address.';
        }

        if (User::query()->where('email', $email)->exists()) {
            return 'This email is already taken.';
        }

        return null;
    }

    private function validateAdminPassword(string $password): ?string
    {
        if (mb_strlen($password) < 8) {
            return 'Password must be at least 8 characters long.';
        }

        return null;
    }
}
