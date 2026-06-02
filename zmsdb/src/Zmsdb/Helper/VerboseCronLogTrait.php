<?php

namespace BO\Zmsdb\Helper;

trait VerboseCronLogTrait
{
    /**
     * @return array{0: string, 1: string}
     */
    protected static function resolveCronLogLevel(string $message, string $level): array
    {
        if (preg_match('/^(DEBUG|INFO|NOTICE|WARN(?:ING)?|ERROR|CRITICAL|ALERT|EMERGENCY):\s*(.*)$/s', $message, $matches)) {
            $level = $matches[1];
            $message = $matches[2];
        }
        $level = \BO\Slim\Bootstrap::normalizeLogLevelName($level);

        return [$level, $message];
    }

    protected function writeVerboseCronLog(string $message, string $level = 'info'): void
    {
        if (!$this->verbose) {
            return;
        }
        [$level, $message] = self::resolveCronLogLevel($message, $level);
        \App::$log->{$level}($message);
    }
}
