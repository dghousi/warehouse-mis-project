<?php

declare(strict_types=1);

namespace App\Console\Commands\Frontend;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Console\Output\OutputInterface;

final class Logger
{
    /*
    * TODO: Needs refactoring. NOSONAR
    */
    public function info(string $message, Command $command): void
    {
        $config = config('frontend_generator.logging');
        $logLevel = $config['level'] ?? 'info';
        $isVerbose = $command->getOutput()->getVerbosity() >= OutputInterface::VERBOSITY_VERBOSE;

        if ($logLevel === 'info' || $logLevel === 'debug' || $isVerbose) {
            Log::channel($config['channel'])->info($message);
        }

        if ($isVerbose) {
            $command->info($message);
        }
    }

    public function error(string $message, Command $command): void
    {
        $config = config('frontend_generator.logging');
        Log::channel($config['channel'])->error($message);
        $command->error($message);
    }
}
