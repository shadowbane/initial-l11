<?php

namespace App\Models\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait HasCreatorTrait
{
    public static function bootHasCreatorTrait(): void
    {
        static::creating(function (Model $model) {
            $model->created_by = auth()->user() ? auth()->user()->uuid : null;
            $model->updated_by = auth()->user() ? auth()->user()->uuid : null;
        });
    }

    /**
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'uuid');
    }
}
