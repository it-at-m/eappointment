<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Repository;

use BO\Zmscitizenbackend\Connection\Pdo;
use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Mail\Repository\IcsRepository;
use BO\Zmscitizenbackend\Core\Service\ExceptionService;

class MyAppointmentsRepository
{
    /**
     * Match zmsbackend ProcessListByExternalUserId when the citizen client omits limit.
     */
    private const int LIST_LIMIT = 100;

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
     * @return list<ThinnedProcess>
     */
    public function readAppointmentsForUser(
        string $externalUserId,
        ?int $filterId = null,
        ?string $status = null
    ): array {
        try {
            $pdo = Select::getReadConnection();
            $params = ['externalUserId' => $externalUserId];
            if ($filterId !== null) {
                $params['filterId'] = $filterId;
            }
            if ($status !== null) {
                $params['status'] = $status;
            }

            $processRows = $pdo->fetchAll(self::processSql($filterId, $status), $params);
            $processRows = is_array($processRows) ? $processRows : [];
            if ($processRows === []) {
                return [];
            }

            $processIds = [];
            foreach ($processRows as $processRow) {
                $processIds[] = (int) ($processRow['process_id'] ?? 0);
            }
            $requestRowsByProcessId = self::readRequestsByProcessId($pdo, $processIds);

            $hydrator = new AppointmentByIdHydrator();
            $appointments = [];
            foreach ($processRows as $processRow) {
                $processId = (int) ($processRow['process_id'] ?? 0);
                $appointment = $hydrator->hydrate(
                    $processRow,
                    $requestRowsByProcessId[$processId] ?? []
                );
                if ($appointment->captchaToken === null) {
                    $appointment->setCaptchaToken('');
                }
                IcsRepository::create()->attachIcs($appointment);
                $appointments[] = $appointment;
            }

            return $appointments;
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    /**
     * @param list<int> $processIds
     * @return array<int, list<array<string, mixed>>>
     */
    private static function readRequestsByProcessId(Pdo $pdo, array $processIds): array
    {
        [$placeholders, $params] = self::processIdPlaceholders($processIds);
        if ($placeholders === '') {
            return [];
        }

        $requestRows = $pdo->fetchAll(self::requestsSql($placeholders), $params);
        $requestRows = is_array($requestRows) ? $requestRows : [];

        $byProcessId = [];
        foreach ($requestRows as $requestRow) {
            $processId = (int) ($requestRow['process_id'] ?? 0);
            $byProcessId[$processId][] = $requestRow;
        }

        return $byProcessId;
    }

    /**
     * Column aliases must match AppointmentByIdHydrator.
     */
    private static function processSql(?int $filterId, ?string $status): string
    {
        $sql = <<<SQL
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
WHERE process.external_user_id = :externalUserId
SQL;

        if ($filterId !== null) {
            $sql .= <<<SQL

    AND process.BuergerID = :filterId
    AND (process.istFolgeterminvon IS NULL OR process.istFolgeterminvon = 0)
SQL;
        }
        if ($status !== null) {
            $sql .= "\n    AND process.status = :status";
        }

        return $sql . "\nLIMIT " . self::LIST_LIMIT;
    }

    private static function requestsSql(string $placeholders): string
    {
        return <<<SQL
SELECT
    buergeranliegen.BuergerID AS process_id,
    request.id,
    request.name,
    request.source
FROM buergeranliegen
INNER JOIN request
    ON request.id = buergeranliegen.AnliegenID
    AND request.source = buergeranliegen.source
WHERE buergeranliegen.BuergerID IN ($placeholders)
ORDER BY buergeranliegen.BuergeranliegenID ASC
SQL;
    }

    /**
     * @param list<int> $processIds
     * @return array{0: string, 1: array<string, int>}
     */
    private static function processIdPlaceholders(array $processIds): array
    {
        $placeholders = [];
        $params = [];
        foreach (array_values($processIds) as $index => $processId) {
            if ($processId <= 0) {
                continue;
            }
            $key = 'processId' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $processId;
        }

        return [implode(', ', $placeholders), $params];
    }
}
