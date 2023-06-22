<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Traits\HasUuid;

class Image extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $table = 'images';

    protected $guarded = [];

    protected $appends = ['url'];

    public function imageable(): MorphTo
    {
        return $this->morphTo('imageable');
    }

    protected function url(): Attribute
    {
        return new Attribute(
            get: fn () => Storage::disk('public')->url($this->path)
        );
    }
}
