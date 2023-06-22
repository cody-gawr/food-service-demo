<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class FollowerFollowing extends Pivot
{
    protected $table = 'follower_following';
}
