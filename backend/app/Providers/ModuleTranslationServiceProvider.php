<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;

final class ModuleTranslationServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $modulesPath = base_path('app/Modules');
        if (!File::isDirectory($modulesPath)) {
            return;
        }

        $modules = File::directories($modulesPath);
        foreach ($modules as $module) {
            $langPath = "{$module}/Resources/lang";
            if (File::isDirectory($langPath)) {
                $this->loadTranslationsFrom($langPath, basename((string) $module));
            }
        }
    }
}
