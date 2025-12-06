<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use BezhanSalleh\FilamentShield\Support\Utils;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

use function Laravel\Prompts\password;
use function Laravel\Prompts\text;

final class CreateAdminUser extends Command
{
    protected $signature = 'app:create-admin
                            {--firstname= : Admin user first name}
                            {--lastname= : Admin user last name}
                            {--username= : Admin username (used for email generation)}
                            {--password= : Admin user password}';

    protected $description = 'Create a new admin user for the application';

    public function handle(): int
    {
        $this->info('👤 Creating an Admin user...');

        $credentials = $this->getAdminCredentials();

        if ($credentials === null) {
            return self::FAILURE;
        }

        /** @var User $admin */
        $admin = User::query()->create([
            'firstname' => $credentials['firstname'],
            'lastname' => $credentials['lastname'],
            'email' => $credentials['email'],
            'password' => $credentials['password'],
            'email_verified_at' => now(),
        ]);

        $admin->assignRole($this->getAdminRoleName());

        $this->newLine();
        $this->info("✅ Admin user '{$admin->name}' created and assigned the '{$this->getAdminRoleName()}' role successfully.");

        return self::SUCCESS;
    }

    /**
     * @return array{firstname: string, lastname: string, email: string, password: string}|null
     */
    private function getAdminCredentials(): ?array
    {
        /** @var ?string $firstname */
        $firstname = $this->option('firstname');
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
        $lastname = $this->option('lastname');

        if (blank($lastname)) {
            $lastname = text(
                label: 'Last name',
                placeholder: 'Doe',
                required: true
            );
        }

        /** @var ?string $username */
        $username = $this->option('username');

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

                return null;
            }
        }

        $email = "{$username}@{$domain}";

        $emailValidation = $this->validateAdminEmail($email);

        if ($emailValidation) {
            $this->error("Invalid email: {$emailValidation}");

            return null;
        }

        /** @var ?string $passwordInput */
        $passwordInput = $this->option('password');

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

                return null;
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
