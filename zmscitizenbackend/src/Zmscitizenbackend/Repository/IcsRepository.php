<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

use BO\Slim\LoggerService;
use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Helper\MailTemplateProvider;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Services\Core\MapperService;
use BO\Zmsentities\Config;
use BO\Zmsentities\Helper\Messaging;
use BO\Zmsentities\Process;

class IcsRepository
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

    public function attachIcs(ThinnedProcess $appointment): void
    {
        $icsContent = $this->readIcsContent($appointment);
        if ($icsContent) {
            $appointment->setIcsContent($icsContent);
        }
    }

    public function readIcsContent(ThinnedProcess $appointment): ?string
    {
        $hydrator = new AppointmentByIdHydrator();
        if (!$hydrator->shouldGenerateIcs($appointment->timestamp, $appointment->status)) {
            return null;
        }
        if (($appointment->authKey ?? '') === '') {
            return null;
        }

        try {
            return $this->readIcsContentForProcess(MapperService::thinnedProcessToProcess($appointment));
        } catch (\Exception $exception) {
            LoggerService::logError($exception, null, null, [
                'processId' => $appointment->processId,
                'context' => 'ICS render',
            ]);
            return null;
        }
    }

    public function readIcsContentForProcess(Process $process): ?string
    {
        try {
            $provider = $process->scope->provider ?? null;
            $providerId = 0;
            if (is_array($provider) || $provider instanceof \ArrayAccess) {
                $providerId = (int) ($provider['id'] ?? 0);
            } elseif (is_object($provider) && isset($provider->id)) {
                $providerId = (int) $provider->id;
            }
            $templates = MailTemplatesRepository::create()->readMergedTemplatesForProvider($providerId);
            $config = $this->readConfig();
            $ics = Messaging::getMailIcs(
                $process,
                $config,
                'appointment',
                null,
                false,
                MailTemplateProvider::withDefaults($templates)
            );

            $content = $ics->getContent();
            return is_string($content) && $content !== '' ? $content : null;
        } catch (\Exception $exception) {
            LoggerService::logError($exception, null, null, [
                'processId' => $process->getId(),
                'context' => 'ICS render',
            ]);
            return null;
        }
    }

    private function readConfig(): Config
    {
        $pdo = Select::getReadConnection();
        $rows = $pdo->fetchAll(IcsQueries::QUERY_SELECT_CONFIG, []);
        $rows = is_array($rows) ? $rows : [];

        $hash = [];
        foreach ($rows as $row) {
            $name = (string) ($row['name'] ?? '');
            if ($name === '') {
                continue;
            }
            $hash[$name] = $row['value'] ?? '';
        }

        return new Config($hash);
    }
}
