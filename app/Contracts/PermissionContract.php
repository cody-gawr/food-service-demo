<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface PermissionContract
{
    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany;

    /**
     * @param string  $name
     * @return \App\Contracts\PermissionContract
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public static function findByName(string $name): self;

    /**
     * @param int  $id
     * @return \App\Contracts\PermissionContract
     * @throws \App\Exceptions\Permission\PermissionDoesNotExist
     */
    public static function findById(int $id): self;

    /**
     * @param string $name
     * @return \App\Contracts\PermissionContract
     */
    public static function findOrCreate(string $name): self;
}
