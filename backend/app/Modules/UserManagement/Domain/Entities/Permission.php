<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Domain\Entities;

use Spatie\Permission\Models\Permission as SpatiePermission;

final class Permission extends SpatiePermission
{
    protected $fillable = [
        'guard_name',
        'name',
        'display_name_en',
        'display_name_ps',
        'display_name_dr',
    ];
}
