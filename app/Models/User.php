<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Casts\UTCDateTime;
use App\Models\Traits\CanImpersonateTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property string $ulid
 * @property string $name
 * @property-write string $password
 * @property-read Carbon $created_at
 * @property-read Carbon $updated_at
 */
class User extends Authenticatable
{
    use CanImpersonateTrait;
    use CrudTrait;
    use HasFactory;
    use HasRoles;
    use HasUlids;
    use Notifiable;
    use \Illuminate\Database\Eloquent\Concerns\HasTimestamps, \App\Models\Traits\CustomTimestampsTrait {
        \App\Models\Traits\CustomTimestampsTrait::freshTimestamp insteadof \Illuminate\Database\Eloquent\Concerns\HasTimestamps;
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'last_login_ip',
        'last_login',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

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
            'last_login' => UTCDateTime::class,
            'must_change_password' => 'boolean',
        ];
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param  string|int|array|Role|Collection|\BackedEnum  $roles
     */
    public function hasRole($roles, ?string $guard = null): bool
    {
        return once(function () use ($roles, $guard) {
            $this->loadMissing('roles');

            if (is_string($roles) && strpos($roles, '|') !== false) {
                $roles = $this->convertPipeToArray($roles);
            }

            if ($roles instanceof \BackedEnum) {
                $roles = $roles->value;
            }

            if (is_int($roles) || PermissionRegistrar::isUid($roles)) {
                $key = (new ($this->getRoleClass())())->getKeyName();

                return $guard
                    ? $this->roles->where('guard_name', $guard)->contains($key, $roles)
                    : $this->roles->contains($key, $roles);
            }

            if (is_string($roles)) {
                return $guard
                    ? $this->roles->where('guard_name', $guard)->contains('name', $roles)
                    : $this->roles->contains('name', $roles);
            }

            if ($roles instanceof Role) {
                return $this->roles->contains($roles->getKeyName(), $roles->getKey());
            }

            if (is_array($roles)) {
                foreach ($roles as $role) {
                    if ($this->hasRole($role, $guard)) {
                        return true;
                    }
                }

                return false;
            }

            if ($roles instanceof Collection) {
                return $roles->intersect($guard ? $this->roles->where('guard_name', $guard) : $this->roles)->isNotEmpty();
            }

            throw new \TypeError('Unsupported type for $roles parameter to hasRole().');
        });
    }
}
