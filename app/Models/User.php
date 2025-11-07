<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasPublicId;
use BezhanSalleh\FilamentShield\Support\Utils;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property-read int $id
 * @property-read string $public_id
 * @property-read string $firstname
 * @property-read string $lastname
 * @property-read string $name
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements FilamentUser, MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasPanelShield, HasPublicId, HasRoles, Notifiable;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        /** @var string $domain */
        $domain = config('app.domain');

        if (
            str_ends_with($this->email, '@'.$domain)
            && $this->hasVerifiedEmail()
            && $this->isAdmin()
        ) {
            return true;
        }

        return false;
    }

    public function isAdmin(): bool
    {
        if ($this->hasRole(Utils::getSuperAdminName())) {
            return true;
        }

        return $this->hasRole(Utils::getPanelUserRoleName());
    }

    public function getFilamentName(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    /**
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => $this->getFilamentName());
    }
}
