<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository\Office;

use BO\Zmscitizenbackend\Connection\Select;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Services\Core\ExceptionService;
use BO\Zmscitizenbackend\Utils\SourceNames;

class OfficesServicesRelationsRepository
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

    public function readOfficesAndServices(bool $showUnpublished = false): OfficeServiceAndRelationList
    {
        try {
            $sources = SourceNames::configured();
            $pdo = Select::getReadConnection();
            [$placeholders, $params] = self::sourcePlaceholders($sources);

            $officeRows = $pdo->fetchAll(self::officesSql($placeholders), $params);
            $requestRows = $pdo->fetchAll(self::requestsSql($placeholders), $params);
            $relationRows = $pdo->fetchAll(self::relationsSql($placeholders), $params);

            return (new OfficesServicesRelationsHydrator())->hydrate(
                is_array($officeRows) ? $officeRows : [],
                is_array($requestRows) ? $requestRows : [],
                is_array($relationRows) ? $relationRows : [],
                $showUnpublished
            );
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    /**
     * @return list<string>
     */
    public function readServiceIdsByOfficeId(int $officeId, bool $showUnpublished = false): array
    {
        try {
            $sources = SourceNames::configured();
            $pdo = Select::getReadConnection();
            [$placeholders, $params] = self::sourcePlaceholders($sources);
            $params['officeId'] = $officeId;

            $rows = $pdo->fetchAll(self::servicesByOfficeSql($placeholders), $params);
            $rows = is_array($rows) ? $rows : [];

            $serviceIds = [];
            foreach ($rows as $row) {
                if (!$showUnpublished && self::isUnpublishedRequest($row['request_data'] ?? null)) {
                    continue;
                }
                $serviceId = (string) ($row['service_id'] ?? '');
                if ($serviceId !== '' && !in_array($serviceId, $serviceIds, true)) {
                    $serviceIds[] = $serviceId;
                }
            }

            return $serviceIds;
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    public function isCaptchaRequiredForOfficeIds(array $officeIds): bool
    {
        $wanted = [];
        foreach ($officeIds as $officeIdRaw) {
            $officeId = (int) $officeIdRaw;
            if ($officeId > 0) {
                $wanted[$officeId] = $officeId;
            }
        }
        if ($wanted === []) {
            return false;
        }

        try {
            $sources = SourceNames::configured();
            $pdo = Select::getReadConnection();
            [$sourcePlaceholders, $params] = self::sourcePlaceholders($sources);
            [$officePlaceholders, $officeParams] = self::officePlaceholders(array_values($wanted));
            $params = array_merge($params, $officeParams);

            $value = $pdo->fetchValue(
                self::captchaRequiredByOfficesSql($sourcePlaceholders, $officePlaceholders),
                $params
            );

            return $value !== null && $value !== false && (int) $value === 1;
        } catch (\Exception $exception) {
            ExceptionService::handleException($exception);
        }
    }

    /**
     * @param list<string> $sources
     * @return array{0: string, 1: array<string, string|int>}
     */
    private static function sourcePlaceholders(array $sources): array
    {
        $placeholders = [];
        $params = [];
        foreach ($sources as $index => $source) {
            $key = 'source' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $source;
        }
        return [implode(', ', $placeholders), $params];
    }

    /**
     * @param list<int> $officeIds
     * @return array{0: string, 1: array<string, int>}
     */
    private static function officePlaceholders(array $officeIds): array
    {
        $placeholders = [];
        $params = [];
        foreach ($officeIds as $index => $officeId) {
            $key = 'office' . $index;
            $placeholders[] = ':' . $key;
            $params[$key] = $officeId;
        }
        return [implode(', ', $placeholders), $params];
    }

    private static function officesSql(string $placeholders): string
    {
        return <<<SQL
SELECT
    provider.id,
    provider.source,
    provider.name,
    provider.display_name,
    provider.parent_id,
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
FROM provider
LEFT JOIN standort AS scope
    ON scope.InfoDienstleisterID = provider.id
    AND scope.source = provider.source
LEFT JOIN email
    ON email.BehoerdenID = scope.BehoerdenID
WHERE provider.source IN ({$placeholders})
ORDER BY provider.source, provider.id, scope.StandortID
SQL;
    }

    private static function requestsSql(string $placeholders): string
    {
        return <<<SQL
SELECT
    request.id,
    request.source,
    request.name,
    request.parent_id,
    request.variant_id,
    request.data
FROM request
WHERE request.source IN ({$placeholders})
ORDER BY request.id
SQL;
    }

    private static function relationsSql(string $placeholders): string
    {
        return <<<SQL
SELECT
    request_provider.provider__id,
    request_provider.request__id,
    request_provider.source,
    request_provider.slots,
    request_provider.public_visibility,
    request_provider.max_quantity
FROM request_provider
WHERE request_provider.source IN ({$placeholders})
    AND request_provider.bookable = 1
SQL;
    }

    private static function servicesByOfficeSql(string $placeholders): string
    {
        return <<<SQL
SELECT
    request.id AS service_id,
    request.data AS request_data
FROM request_provider
INNER JOIN request
    ON request.id = request_provider.request__id
    AND request.source = request_provider.source
WHERE request_provider.provider__id = :officeId
    AND request_provider.bookable = 1
    AND request_provider.source IN ({$placeholders})
ORDER BY request.id
SQL;
    }

    private static function captchaRequiredByOfficesSql(string $sourcePlaceholders, string $officePlaceholders): string
    {
        return <<<SQL
SELECT 1
FROM standort AS scope
INNER JOIN provider
    ON provider.id = scope.InfoDienstleisterID
    AND provider.source = scope.source
WHERE provider.id IN ({$officePlaceholders})
    AND provider.source IN ({$sourcePlaceholders})
    AND scope.captcha_activated_required != 0
LIMIT 1
SQL;
    }

    private static function isUnpublishedRequest(mixed $requestData): bool
    {
        $data = $requestData;
        if (is_string($requestData) && $requestData !== '') {
            $decoded = json_decode($requestData, true);
            $data = is_array($decoded) ? $decoded : [];
        }
        if (!is_array($data) || !isset($data['public'])) {
            return false;
        }

        return !$data['public'];
    }
}
