<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

use BO\Zmscitizenbackend\Connection\Pdo;
use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Exceptions\EmailRequired;
use BO\Zmscitizenbackend\Helper\MailTemplateProvider;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
use BO\Zmscitizenbackend\Services\Core\MapperService;
use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Config;
use BO\Zmsentities\Department;
use BO\Zmsentities\Mail;
use BO\Zmsentities\Process;

class MailQueueRepository
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

    public function queueConfirmationMail(ThinnedProcess $appointment): void
    {
        $this->queue($appointment, 'confirmation');
    }

    public function queuePreconfirmationMail(ThinnedProcess $appointment): void
    {
        $this->queue($appointment, 'preconfirmation');
    }

    public function queueCancellationMail(ThinnedProcess $appointment): void
    {
        $this->queue($appointment, 'cancellation');
    }

    private function queue(ThinnedProcess $appointment, string $kind): void
    {
        try {
            $process = MapperService::thinnedProcessToProcess($appointment);
            $this->assertEmailWhenRequired($process);
            if (!$process->getFirstClient()->hasEmail() || !$process->scope->hasEmailFrom()) {
                return;
            }

            $status = $this->mailStatus($process, $kind);
            $mail = $this->buildMail($process, $status);
            $this->writeInQueue($mail);
        } catch (EmailRequired $exception) {
            throw $exception;
        } catch (\RuntimeException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    private function assertEmailWhenRequired(Process $process): void
    {
        $emailRequired = (bool) $process->toProperty()->scope->preferences->client->emailRequired->get();
        if ($emailRequired && !$process->getFirstClient()->hasEmail()) {
            throw new EmailRequired();
        }
    }

    private function mailStatus(Process $process, string $kind): string
    {
        return match ($kind) {
            'confirmation' => $process->isWithAppointment() ? 'appointment' : 'queued',
            'preconfirmation' => $process->isWithAppointment() ? 'preconfirmed' : 'queued',
            default => 'deleted',
        };
    }

    private function buildMail(Process $process, string $status): Mail
    {
        $provider = $process->scope->provider ?? null;
        $providerId = 0;
        if (is_array($provider) || $provider instanceof \ArrayAccess) {
            $providerId = (int) ($provider['id'] ?? 0);
        } elseif (is_object($provider) && isset($provider->id)) {
            $providerId = (int) $provider->id;
        }

        $templates = MailTemplatesRepository::create()->readMergedTemplatesForProvider($providerId);
        $config = $this->readConfig();
        $department = new Department(['id' => $this->readDepartmentId((int) $process->getScopeId())]);
        $collection = (new ProcessList())->addEntity($process);

        return (new Mail())
            ->setTemplateProvider(MailTemplateProvider::withDefaults($templates))
            ->toResolvedEntity($collection, $config, $status)
            ->withDepartment($department);
    }

    private function writeInQueue(Mail $mail): void
    {
        $client = $mail->getFirstClient();
        $pdo = Select::getWriteConnection();
        $now = \App::$now instanceof \DateTimeInterface ? \App::$now : new \DateTimeImmutable();

        $pdo->perform(MailQueueQueries::QUERY_INSERT_QUEUE, [
            'processId' => (int) $mail->getProcessId(),
            'departmentId' => (int) ($mail->department->getId() ?? 0),
            'createIP' => (string) ($mail->createIP ?? ''),
            'createTimestamp' => (int) $now->format('U'),
            'subject' => (string) ($mail->subject ?? ''),
            'clientFamilyName' => (string) ($client->familyName ?? ''),
            'clientEmail' => (string) ($client->email ?? ''),
        ]);
        $queueId = (int) $pdo->lastInsertId();
        if ($queueId < 1) {
            throw new \RuntimeException('Failed to write mail queue');
        }

        $this->writeMimeParts($pdo, $queueId, $mail);
    }

    private function writeMimeParts(Pdo $pdo, int $queueId, Mail $mail): void
    {
        foreach ($mail->multipart ?? [] as $part) {
            $mime = (string) ($part['mime'] ?? '');
            $content = (string) ($part['content'] ?? '');
            if ($mime === '' || $content === '') {
                $pdo->perform(MailQueueQueries::QUERY_DELETE_QUEUE, ['queueId' => $queueId]);
                throw new \RuntimeException('Failed to write part (' . $mime . ') of mail with id ' . $queueId);
            }
            $pdo->perform(MailQueueQueries::QUERY_INSERT_PART, [
                'queueId' => $queueId,
                'mime' => $mime,
                'content' => $content,
                'base64' => !empty($part['base64']) ? 1 : 0,
            ]);
        }
    }

    private function readDepartmentId(int $scopeId): int
    {
        if ($scopeId < 1) {
            return 0;
        }
        $row = Select::getReadConnection()->fetchOne(MailQueueQueries::QUERY_SELECT_DEPARTMENT, [
            'scopeId' => $scopeId,
        ]);
        if (!is_array($row)) {
            return 0;
        }
        return (int) ($row['department_id'] ?? 0);
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
