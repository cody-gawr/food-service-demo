<?php

namespace App\Traits;

use Illuminate\Support\Str;

trait HasUuid {
    protected static function boot()
    {
        // Boot other traits on the Model
        parent::boot();

        /**
         * Listen for the creating event on the user model.
         * Sets the 'uuid' to a UUID using Str::uuid() on the instance being created
         */
        static::creating(function ($model) {
            $model->setAttribute('uuid', Str::uuid()->toString());
        });
    }
}
