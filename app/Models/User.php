<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Traits\CanImpersonateTrait;
use Backpack\CRUD\app\Models\Traits\CrudTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
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
        ];
    }
}
