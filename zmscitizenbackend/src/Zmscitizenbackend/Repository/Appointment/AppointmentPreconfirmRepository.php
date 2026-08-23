<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository\Appointment;

use BO\Zmscitizenbackend\Connection\Pdo;
use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Exceptions\MoreThanAllowedAppointmentsPerMail;
use BO\Zmscitizenbackend\Exceptions\ProcessNotFound;
use BO\Zmscitizenbackend\Exceptions\ProcessNotReservedAnymore;
use BO\Zmscitizenbackend\Exceptions\UnauthorizedException;
use BO\Zmscitizenbackend\Models\ThinnedProcess;
use BO\Zmscitizenbackend\Repository\Mail\IcsRepository;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;

class AppointmentPreconfirmRepository
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

    public function preconfirmAppointment(ThinnedProcess $process): ThinnedProcess
    {
        try {
            $processId = (int) ($process->processId ?? 0);
            if ($processId < 1) {
                throw new ProcessNotFound();
            }

            $pdo = Select::getWriteConnection();
            $this->persistPreconfirmedStatus($pdo, $process, $processId);
            $appointment = $this->reloadAppointment($pdo, $processId);
            IcsRepository::create()->attachIcs($appointment);

            return $appointment;
        } catch (\Exception $exception) {
            if ($exception instanceof UnauthorizedException) {
                throw $exception;
            }
            ExceptionService::handleException($exception);
        }
    }

    private function persistPreconfirmedStatus(Pdo $pdo, ThinnedProcess $process, int $processId): void
    {
        $startedTransaction = false;
        if (!$pdo->inTransaction()) {
            $pdo->beginTransaction();
            $startedTransaction = true;
        }

        try {
            $locked = $pdo->fetchOne(AppointmentPreconfirmQueries::QUERY_LOCK_PROCESS, [
                'processId' => $processId,
            ]);
            if (!is_array($locked) || $locked === []) {
                throw new ProcessNotFound();
            }

            if ((string) ($locked['status'] ?? '') !== 'reserved') {
                throw new ProcessNotReservedAnymore();
            }

            $this->assertMailLimit($pdo, $process);

            $pdo->perform(AppointmentPreconfirmQueries::QUERY_PRECONFIRM_PROCESS, [
                'processId' => $processId,
            ]);
            $pdo->perform(AppointmentPreconfirmQueries::QUERY_UPDATE_FOLLOWING_PROCESS, [
                'processId' => $processId,
            ]);

            if ($startedTransaction) {
                $pdo->commit();
            }
        } catch (\Exception $exception) {
            if ($startedTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function assertMailLimit(Pdo $pdo, ThinnedProcess $process): void
    {
        $email = trim((string) ($process->email ?? ''));
        $maxAppointmentsPerMail = (int) ($process->scope?->appointmentsPerMail ?? 0);
        if ($maxAppointmentsPerMail < 1 || $email === '') {
            return;
        }

        if ($this->isMailWhitelisted($email, $process->scope?->whitelistedMails)) {
            return;
        }

        $rows = $pdo->fetchAll(AppointmentUpdateQueries::QUERY_SELECT_PROCESSES_BY_MAIL, [
            'email' => $email,
            'scopeId' => (int) ($process->scope?->id ?? 0),
        ]);
        $rows = is_array($rows) ? $rows : [];

        $processId = (int) ($process->processId ?? 0);
        $activeAppointments = 0;
        foreach ($rows as $row) {
            if ((int) ($row['process_id'] ?? 0) === $processId) {
                return;
            }
            if (in_array($row['status'] ?? '', ['preconfirmed', 'confirmed'], true)) {
                $activeAppointments++;
                if ($activeAppointments >= $maxAppointmentsPerMail) {
                    throw new MoreThanAllowedAppointmentsPerMail();
                }
            }
        }
    }

    private function isMailWhitelisted(string $email, ?string $whitelistedMails): bool
    {
        if ($whitelistedMails === null || trim($whitelistedMails) === '') {
            return false;
        }

        foreach (explode(',', $whitelistedMails) as $whitelistedMail) {
            $whitelistedMail = trim($whitelistedMail);
            if ($whitelistedMail === '') {
                continue;
            }
            if ($email === $whitelistedMail) {
                return true;
            }
            if (str_starts_with($whitelistedMail, '@') && str_contains($email, $whitelistedMail)) {
                return true;
            }
        }

        return false;
    }

    private function reloadAppointment(Pdo $pdo, int $processId): ThinnedProcess
    {
        $processRow = $pdo->fetchOne(self::processSql(), ['processId' => $processId]);
        if (!is_array($processRow) || $processRow === []) {
            throw new ProcessNotFound();
        }
        $requestRows = $pdo->fetchAll(self::requestsSql(), ['processId' => $processId]);
        $requestRows = is_array($requestRows) ? $requestRows : [];

        $appointment = (new AppointmentByIdHydrator())->hydrate($processRow, $requestRows);
        if ($appointment->captchaToken === null) {
            $appointment->setCaptchaToken('');
        }

        return $appointment;
    }

    private static function processSql(): string
    {
        return <<<SQL
SELECT
    process.BuergerID AS process_id,
    process.absagecode AS auth_key,
    process.external_user_id AS external_user_id,
    process.Name AS family_name,
    process.custom_text_field AS custom_textfield,
    process.custom_text_field2 AS custom_textfield2,
    process.EMail AS email,
    IF(
        process.telefonnummer_fuer_rueckfragen != '',
        process.telefonnummer_fuer_rueckfragen,
        process.Telefonnummer
    ) AS telephone,
    CONCAT(process.Datum, ' ', process.Uhrzeit) AS appointment_datetime,
    (process.hatFolgetermine + 1) AS slot_count,
    COALESCE(
        process.displayNumber,
        IF(process.wartenummer, process.wartenummer, process.BuergerID)
    ) AS display_number,
    CASE
        WHEN process.status = "called"
            AND process.aufrufzeit != "00:00:00"
            AND process.NutzerID != 0
            AND process.AbholortID = 0
            THEN "called"
        WHEN process.status = "called" AND process.Uhrzeit = "00:00:00"
            THEN "queued"
        WHEN process.status = "called"
            AND process.vorlaeufigeBuchung = 0
            AND process.bestaetigt = 1
            THEN "confirmed"
        ELSE process.status
    END AS status,
    provider.id,
    provider.source,
    provider.name,
    provider.display_name,
    provider.data,
    provider.contact__city,
    provider.contact__country,
    provider.contact__lat,
    provider.contact__lon,
    provider.contact__postalCode,
    provider.contact__region,
    provider.contact__street,
    provider.contact__streetNumber,
    scope.StandortID AS scope_id,
    scope.standortkuerzel AS scope_short_name,
    email.absenderadresse AS scope_email_from,
    scope.emailPflichtfeld AS scope_email_required,
    scope.telefonaktiviert AS scope_telephone_activated,
    scope.telefonPflichtfeld AS scope_telephone_required,
    scope.custom_text_field_active AS scope_custom_textfield_activated,
    scope.custom_text_field_required AS scope_custom_textfield_required,
    scope.custom_text_field_label AS scope_custom_textfield_label,
    scope.custom_text_field2_active AS scope_custom_textfield2_activated,
    scope.custom_text_field2_required AS scope_custom_textfield2_required,
    scope.custom_text_field2_label AS scope_custom_textfield2_label,
    scope.captcha_activated_required AS scope_captcha_activated_required,
    scope.info_for_appointment AS scope_info_for_appointment,
    scope.info_for_all_appointments AS scope_info_for_all_appointments,
    scope.slots_per_appointment AS scope_slots_per_appointment,
    scope.appointments_per_mail AS scope_appointments_per_mail,
    scope.whitelisted_mails AS scope_whitelisted_mails,
    scope.reservierungsdauer AS scope_reservation_duration,
    scope.aktivierungsdauer AS scope_activation_duration,
    scope.Hinweis AS scope_hint
FROM buerger AS process
LEFT JOIN standort AS scope
    ON scope.StandortID = IF(process.AbholortID, process.AbholortID, process.StandortID)
LEFT JOIN provider
    ON provider.id = scope.InfoDienstleisterID
    AND provider.source = scope.source
LEFT JOIN email
    ON email.BehoerdenID = scope.BehoerdenID
WHERE process.BuergerID = :processId
    AND (process.istFolgeterminvon IS NULL OR process.istFolgeterminvon = 0)
SQL;
    }

    private static function requestsSql(): string
    {
        return <<<SQL
SELECT
    request.id,
    request.name,
    request.source
FROM buergeranliegen
INNER JOIN request
    ON request.id = buergeranliegen.AnliegenID
    AND request.source = buergeranliegen.source
WHERE buergeranliegen.BuergerID = :processId
ORDER BY buergeranliegen.BuergeranliegenID ASC
SQL;
    }
}
