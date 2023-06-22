<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Video extends Model
{
    use HasFactory, SoftDeletes, HasUuid;

    protected $table = 'videos';

    protected $guarded = [];

    public function videoable(): MorphTo
    {
        return $this->morphTo('videoable');
    }
}
