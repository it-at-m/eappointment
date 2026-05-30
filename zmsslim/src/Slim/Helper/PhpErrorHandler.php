<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

use Monolog\Logger;
use Psr\Log\LoggerInterface;

final class PhpErrorHandler
{
    public static function register(): void
    {
        set_error_handler([self::class, 'handle']);
    }

    public static function handle(int $severity, string $message, string $file, int $line): bool
    {
        if (!(error_reporting() & $severity)) {
            return false;
        }

        if (!isset(\App::$log) || !\App::$log instanceof LoggerInterface) {
            return false;
        }

        \App::$log->log(self::severityToLogLevel($severity), $message, [
            'php_errno' => $severity,
            'file' => $file,
            'line' => $line,
        ]);

        throw new \ErrorException($message, 0, $severity, $file, $line);
    }

    public static function severityToLogLevel(int $severity): int
    {
        return match ($severity) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE, E_USER_ERROR => Logger::ERROR,
            E_WARNING, E_CORE_WARNING, E_COMPILE_WARNING, E_USER_WARNING => Logger::WARNING,
            E_NOTICE, E_USER_NOTICE, E_STRICT, E_DEPRECATED, E_USER_DEPRECATED => Logger::NOTICE,
            default => Logger::WARNING,
        };
    }
}
