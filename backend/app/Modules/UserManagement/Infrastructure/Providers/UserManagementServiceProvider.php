<?php

declare(strict_types=1);

namespace App\Modules\UserManagement\Infrastructure\Providers;

use App\Modules\UserManagement\Domain\Repositories\PermissionRepositoryInterface;
use App\Modules\UserManagement\Domain\Repositories\RoleRepositoryInterface;
use App\Modules\UserManagement\Domain\Repositories\UserRepositoryInterface;
use App\Modules\UserManagement\Infrastructure\Repositories\EloquentPermissionRepository;
use App\Modules\UserManagement\Infrastructure\Repositories\EloquentRoleRepository;
use App\Modules\UserManagement\Infrastructure\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

final class UserManagementServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerBindings();
    }

    public function boot(): void
    {
        $this->loadRoutesFrom(path: modulePath(module: 'UserManagement', path: 'Routes/api.php'));
    }

    protected function registerBindings(): void
    {
        $this->app->bind(abstract: UserRepositoryInterface::class, concrete: EloquentUserRepository::class);
        $this->app->bind(abstract: RoleRepositoryInterface::class, concrete: EloquentRoleRepository::class);
        $this->app->bind(abstract: PermissionRepositoryInterface::class, concrete: EloquentPermissionRepository::class);
    }
}
