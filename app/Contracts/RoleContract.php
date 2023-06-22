<?php

namespace App\Contracts;

use Illuminate\Database\Eloquent\Relations\BelongsToMany;

interface RoleContract
{
    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany;

    /**
     * Find a role by its name
     * @param string  $name
     * @return \App\Contracts\RoleContract;
     * @throws \App\Exceptions\Permission\RoleDoesNotExist
     */
    public static function findByName(string $name): self;

    /**
     * @param int  $id
     * @return \App\Contracts\RoleContract
     * @throws \App\Exceptions\Permission\RoleDoesNotExist
     */
    public static function findById(int $id): self;

    /**
     * Find or create a role by its name and guard name.
     *
     * @param  string  $name
     * @return \App\Contracts\RoleContract
     */
    public static function findOrCreate(string $name): self;
}
