<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;

class MailTemplatesRepository
{
    private static ?self $instance = null;

    public static function use(?self $repository): void
    {
        self::$instance = $repository;
    }

    public static function create(): self
    {
        return self::$instance ?? new self();
    }

    /**
     * @return array<string, string>
     */
    public function readMergedTemplatesForProvider(int $providerId): array
    {
        try {
            $pdo = Select::getReadConnection();
            $generalRows = $pdo->fetchAll(MailTemplatesQueries::QUERY_SELECT_WITHOUT_PROVIDER, []);
            $generalRows = is_array($generalRows) ? $generalRows : [];

            $customRows = [];
            if ($providerId > 0) {
                $customRows = $pdo->fetchAll(MailTemplatesQueries::QUERY_SELECT_BY_PROVIDER, [
                    'providerId' => (string) $providerId,
                ]);
                $customRows = is_array($customRows) ? $customRows : [];
            }

            return $this->mergeTemplates($generalRows, $customRows);
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    /**
     * @param list<array<string, mixed>> $generalRows
     * @param list<array<string, mixed>> $customRows
     * @return array<string, string>
     */
    private function mergeTemplates(array $generalRows, array $customRows): array
    {
        $templates = [];
        foreach ($generalRows as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $templates[$name] = (string) ($row['value'] ?? '');
        }
        foreach ($customRows as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $templates[$name] = (string) ($row['value'] ?? '');
        }

        return $templates;
    }
}
