<?php

namespace App\Models\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Support\Facades\Date;

trait CustomTimestampsTrait
{
    use HasTimestamps;

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function freshTimestamp(): \Illuminate\Support\Carbon
    {
        return Date::now('UTC');
    }

    /**
     * Get created at timestamp.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: static fn($value) => Carbon::parse($value, 'UTC')
                ->setTimezone(config('app.timezone')),
        );
    }

    /**
     * Get updated at timestamp.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: static fn($value) => Carbon::parse($value, 'UTC')
                ->setTimezone(config('app.timezone')),
        );
    }
}
