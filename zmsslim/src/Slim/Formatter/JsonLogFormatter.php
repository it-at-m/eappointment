<?php

declare(strict_types=1);

namespace BO\Slim\Formatter;

use BO\Slim\Bootstrap;
use Monolog\Formatter\JsonFormatter;
use Monolog\LogRecord;

/**
 * JSON log line used by CAP/Kubernetes collectors.
 * Monolog 3 processors must return a LogRecord, so the custom field list lives here.
 */
class JsonLogFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $normalized = [
            'time_local' => (new \DateTime())->format('Y-m-d\TH:i:sP'),
            'client_ip' => $_SERVER['REMOTE_ADDR'] ?? '',
            'remote_addr' => $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '',
            'remote_user' => '',
            'application' => defined('\\App::IDENTIFIER') ? \App::IDENTIFIER : 'zms',
            'module' => defined('\\App::MODULE_NAME') ? \App::MODULE_NAME : 'zmsslim',
            'cron' => Bootstrap::isCronLogging(),
            'cron_name' => Bootstrap::getCronLogName(),
            'message' => $record->message,
            'level' => $record->level->getName(),
            'context' => $this->emptyAsObject($this->normalize($record->context)),
            'extra' => $this->emptyAsObject($this->normalize($record->extra)),
        ];

        return $this->toJson($normalized, true) . ($this->isAppendingNewlines() ? "\n" : '');
    }

    private function emptyAsObject(mixed $value): mixed
    {
        return $value === [] ? new \stdClass() : $value;
    }
}
