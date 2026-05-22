<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;

#[Fillable(['name', 'email', 'password', 'role', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    public function units(): BelongsToMany
    {
        return $this->belongsToMany(Unit::class)
            ->withTimestamps();
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isHrd(): bool
    {
        return $this->role === UserRole::Hrd;
    }

    public function isTu(): bool
    {
        return $this->role === UserRole::Tu;
    }

    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    public function canManageEmployees(): bool
    {
        return $this->isAdmin() || $this->isHrd();
    }

    public function canManageStudents(): bool
    {
        return $this->isAdmin() || $this->isTu();
    }

    public function canAccessUnit(?int $unitId): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        if ($unitId === null) {
            return false;
        }

        return $this->units()
            ->whereKey($unitId)
            ->exists();
    }

    /**
     * @return Collection<int, int>
     */
    public function accessibleUnitIds(): Collection
    {
        if ($this->isAdmin()) {
            return Unit::query()->pluck('id');
        }

        return $this->units()->pluck('units.id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }
}
