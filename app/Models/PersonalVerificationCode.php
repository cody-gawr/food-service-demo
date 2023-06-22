<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class PersonalVerificationCode extends Model
{
    use SoftDeletes, HasUuid;

    protected $table = 'personal_verification_codes';

    protected $guarded = [];

    /**
     * @return string|null
     */
    public function getUpdatedAtColumn()
    {
        return null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
