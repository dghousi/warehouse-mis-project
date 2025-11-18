<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

final class FileChecker
{
    public function check(string $path, string $errorMessage, Command $command, Logger $logger): bool
    {
        $logger->info("Checking: {$path}", $command);
        if (!File::exists($path)) {
            $logger->error($errorMessage, $command);

            return false;
        }

        return true;
    }
}
