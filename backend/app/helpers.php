<?php

declare(strict_types=1);

if (!function_exists(function: 'modulePath')) {
    /**
     * Generate a path relative to a module's directory.
     *
     * @param  string  $module  Module name (e.g., 'Auth')
     * @param  string  $path  Additional path within the module (e.g., 'Infrastructure/Routes/api.php')
     */
    function modulePath(string $module, $path = ''): string
    {
        return app_path(sprintf('Modules/%s/%s', $module, $path));
    }
}
