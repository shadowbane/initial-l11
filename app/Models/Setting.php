<?php

namespace App\Models;

use App\Casts\StringEncryption;
use App\Models\Traits\LogsActivity;
use Backpack\Settings\app\Models\Setting as MS;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;
use Illuminate\Support\Facades\Artisan;

/**
 * Class Setting.
 */
class Setting extends MS
{
    use Cachable;
    use LogsActivity;
    use \Illuminate\Database\Eloquent\Concerns\HasTimestamps, \App\Models\Traits\CustomTimestampsTrait {
        \App\Models\Traits\CustomTimestampsTrait::freshTimestamp insteadof \Illuminate\Database\Eloquent\Concerns\HasTimestamps;
    }

    protected $casts = [
        'value' => StringEncryption::class,
        'active' => 'boolean',
        'field' => 'array',
    ];

    public static function boot(): void
    {
        parent::boot();

        static::updating(function ($model) {
            cache()->clear();
            Artisan::call('config:clear');
        });
    }
}
