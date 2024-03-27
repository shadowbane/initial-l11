<?php

namespace App\Models\Traits;

use App\Models\File;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait UploadGeneratedTrait
{
    /**
     * Supporting Documents.
     */
    public function uploads(): MorphMany
    {
        return $this->morphMany(File::class, 'fileable')
            ->where('name', 'NOT LIKE', '%_generated');
    }

    /**
     * Generated Documents.
     */
    public function generated(): MorphOne
    {
        return $this->morphOne(File::class, 'fileable')
            ->where('name', 'LIKE', '%_generated');
    }
}
