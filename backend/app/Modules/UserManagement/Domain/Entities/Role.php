<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Entities;

use Spatie\Permission\Models\Role as SpatieRole;

final class Role extends SpatieRole
{
    protected $fillable = [
        'guard_name',
        'name',
        'display_name_en',
        'display_name_ps',
        'display_name_dr',
    ];

}
