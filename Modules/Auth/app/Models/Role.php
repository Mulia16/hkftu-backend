<?php

namespace Modules\Auth\Models;

use Spatie\Permission\Models\Role as SpatieRole;

class Role extends SpatieRole
{
    protected $table = 'auth.roles';
}
