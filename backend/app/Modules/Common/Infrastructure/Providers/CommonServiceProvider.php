<?php

declare(strict_types=1);

namespace App\Modules\Common\Infrastructure\Providers;

use App\Modules\Common\Domain\Repositories\FileUploadRepositoryInterface;
use App\Modules\Common\Domain\Services\FileUploadService;
use App\Modules\Common\Infrastructure\Repositories\EloquentFileUploadRepository;
use Illuminate\Support\ServiceProvider;

final class CommonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(FileUploadRepositoryInterface::class, EloquentFileUploadRepository::class);
        $this->app->bind(FileUploadService::class, fn (): FileUploadService => new FileUploadService(
            $this->app->make(FileUploadRepositoryInterface::class)
        ));
    }

    public function boot(): void
    {
        // void
    }
}
