<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

use BO\Zmscitizenbackend\Connection\Pdo;
use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Exceptions\EmailRequired;
use BO\Zmscitizenbackend\Helper\MailTemplateRenderer;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;

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
            $this->assertEmailWhenRequired($appointment);
            $email = trim((string) ($appointment->email ?? ''));
            $emailFrom = trim((string) ($appointment->scope?->emailFrom ?? ''));
            if ($email === '' || $emailFrom === '') {
                return;
            }

            $status = $this->mailStatus($appointment, $kind);
            $rendered = MailTemplateRenderer::forAppointment($appointment)->renderMail($appointment, $status);
            $this->writeInQueue($appointment, $rendered);
        } catch (EmailRequired $exception) {
            throw $exception;
        } catch (\RuntimeException $exception) {
            throw $exception;
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    private function assertEmailWhenRequired(ThinnedProcess $appointment): void
    {
        $emailRequired = (bool) ($appointment->scope?->emailRequired);
        $email = trim((string) ($appointment->email ?? ''));
        if ($emailRequired && $email === '') {
            throw new EmailRequired();
        }
    }

    private function mailStatus(ThinnedProcess $appointment, string $kind): string
    {
        $withAppointment = $this->hasAppointmentTime($appointment);

        return match ($kind) {
            'confirmation' => $withAppointment ? 'appointment' : 'queued',
            'preconfirmation' => $withAppointment ? 'preconfirmed' : 'queued',
            default => 'deleted',
        };
    }

    private function hasAppointmentTime(ThinnedProcess $appointment): bool
    {
        $timestamp = (int) ($appointment->timestamp ?? 0);
        if ($timestamp <= 0) {
            return false;
        }

        return date('H:i', $timestamp) !== '00:00';
    }

    /**
     * @param array{
     *     subject: string,
     *     createIP: string,
     *     parts: list<array{mime: string, content: string, base64: bool}>
     * } $rendered
     */
    private function writeInQueue(ThinnedProcess $appointment, array $rendered): void
    {
        $pdo = Select::getWriteConnection();
        $now = \App::$now instanceof \DateTimeInterface ? \App::$now : new \DateTimeImmutable();

        $pdo->perform(MailQueueQueries::QUERY_INSERT_QUEUE, [
            'processId' => (int) $appointment->processId,
            'departmentId' => $this->readDepartmentId((int) ($appointment->scope?->id ?? 0)),
            'createIP' => $rendered['createIP'],
            'createTimestamp' => (int) $now->format('U'),
            'subject' => $rendered['subject'],
            'clientFamilyName' => (string) ($appointment->familyName ?? ''),
            'clientEmail' => (string) ($appointment->email ?? ''),
        ]);
        $queueId = (int) $pdo->lastInsertId();
        if ($queueId < 1) {
            throw new \RuntimeException('Failed to write mail queue');
        }

        $this->writeMimeParts($pdo, $queueId, $rendered['parts']);
    }

    /**
     * @param list<array{mime: string, content: string, base64: bool}> $parts
     */
    private function writeMimeParts(Pdo $pdo, int $queueId, array $parts): void
    {
        foreach ($parts as $part) {
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
}
