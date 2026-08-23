<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Repository;

use BO\Zmscitizenbackend\Office\Model\ThinnedContact;
use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Office\Model\ThinnedProvider;
use BO\Zmscitizenbackend\Office\Model\ThinnedScope;

class AppointmentByIdHydrator
{
    /**
     * @param array<string, mixed> $processRow
     * @param list<array<string, mixed>> $requestRows
     */
    public function hydrate(array $processRow, array $requestRows, ?string $icsContent = null): ThinnedProcess
    {
        $timestamp = $this->timestampFromDatetime($processRow['appointment_datetime'] ?? null);
        $scope = $this->mapScope($processRow);
        $provider = $scope?->provider;
        $contactName = $provider?->contact?->name ?? $provider?->name;
        [$serviceId, $serviceName, $serviceCount, $subRequestCounts] = $this->mapRequests($requestRows);

        $displayNumber = $processRow['display_number'] ?? null;
        $displayNumber = ($displayNumber === null || $displayNumber === '' || $displayNumber === 0 || $displayNumber === '0')
            ? null
            : (string) $displayNumber;

        return new ThinnedProcess(
            processId: (int) ($processRow['process_id'] ?? 0),
            timestamp: $timestamp,
            authKey: isset($processRow['auth_key']) ? (string) $processRow['auth_key'] : null,
            familyName: isset($processRow['family_name']) ? (string) $processRow['family_name'] : null,
            customTextfield: array_key_exists('custom_textfield', $processRow)
                ? (string) ($processRow['custom_textfield'] ?? '')
                : null,
            customTextfield2: array_key_exists('custom_textfield2', $processRow)
                ? (string) ($processRow['custom_textfield2'] ?? '')
                : null,
            email: isset($processRow['email']) ? (string) $processRow['email'] : null,
            telephone: isset($processRow['telephone']) ? (string) $processRow['telephone'] : null,
            officeName: ($contactName !== null && $contactName !== '') ? (string) $contactName : null,
            officeId: (int) ($provider?->id ?? 0),
            scope: $scope,
            subRequestCounts: $subRequestCounts,
            serviceId: $serviceId,
            serviceName: $serviceName,
            serviceCount: $serviceCount,
            status: isset($processRow['status']) ? (string) $processRow['status'] : null,
            slotCount: isset($processRow['slot_count']) ? (int) $processRow['slot_count'] : null,
            displayNumber: $displayNumber,
            icsContent: $icsContent
        );
    }

    public function shouldGenerateIcs(?string $timestamp, ?string $status): bool
    {
        if ($timestamp === null || $timestamp === '' || (int) $timestamp === 0) {
            return false;
        }
        if (date('H:i', (int) $timestamp) === '00:00') {
            return false;
        }

        return !in_array($status, ['deleted', 'blocked'], true);
    }

    /**
     * Match the former zmscitizenapi cancel JSON: zmsbackend re-read the process
     * with resolveReferences=0, so office/provider fields were empty while scope.id stayed.
     */
    public function forCanceledCitizenResponse(ThinnedProcess $appointment): ThinnedProcess
    {
        $scopeId = $appointment->scope?->id ?? 0;
        $source = $appointment->scope?->provider?->source ?? '';

        return new ThinnedProcess(
            processId: $appointment->processId,
            timestamp: $appointment->timestamp,
            authKey: $appointment->authKey,
            familyName: $appointment->familyName,
            customTextfield: $appointment->customTextfield,
            customTextfield2: $appointment->customTextfield2,
            email: $appointment->email,
            telephone: '',
            officeName: null,
            officeId: 0,
            scope: new ThinnedScope(
                id: $scopeId,
                provider: new ThinnedProvider(
                    id: 0,
                    name: '',
                    displayName: null,
                    source: $source,
                    lat: null,
                    lon: null,
                    contact: null
                ),
                shortName: '',
                emailFrom: '',
                whitelistedMails: ''
            ),
            subRequestCounts: $appointment->subRequestCounts,
            serviceId: $appointment->serviceId,
            serviceName: $appointment->serviceName,
            serviceCount: $appointment->serviceCount,
            status: $appointment->status,
            captchaToken: $appointment->captchaToken ?? '',
            slotCount: $appointment->slotCount,
            displayNumber: $appointment->displayNumber,
            icsContent: $appointment->icsContent
        );
    }

    /**
     * @param array<string, mixed> $row
     */
    private function mapScope(array $row): ?ThinnedScope
    {
        if (!isset($row['scope_id']) || $row['scope_id'] === null || $row['scope_id'] === '') {
            return null;
        }

        $providerData = self::decodeJson($row['data'] ?? null);
        $slotsPerAppointment = self::emptyToNull($row['scope_slots_per_appointment'] ?? null);
        $hint = isset($row['scope_hint']) ? trim((string) $row['scope_hint']) : '';

        return new ThinnedScope(
            id: (int) $row['scope_id'],
            provider: $this->mapThinnedProvider($row, $providerData),
            shortName: isset($row['scope_short_name']) ? (string) $row['scope_short_name'] : null,
            emailFrom: isset($row['scope_email_from']) ? (string) $row['scope_email_from'] : null,
            emailRequired: self::toBool($row['scope_email_required'] ?? null),
            telephoneActivated: self::toBool($row['scope_telephone_activated'] ?? null),
            telephoneRequired: self::toBool($row['scope_telephone_required'] ?? null),
            customTextfieldActivated: self::toBool($row['scope_custom_textfield_activated'] ?? null),
            customTextfieldRequired: self::toBool($row['scope_custom_textfield_required'] ?? null),
            customTextfieldLabel: isset($row['scope_custom_textfield_label'])
                ? (string) $row['scope_custom_textfield_label']
                : null,
            customTextfield2Activated: self::toBool($row['scope_custom_textfield2_activated'] ?? null),
            customTextfield2Required: self::toBool($row['scope_custom_textfield2_required'] ?? null),
            customTextfield2Label: isset($row['scope_custom_textfield2_label'])
                ? (string) $row['scope_custom_textfield2_label']
                : null,
            captchaActivatedRequired: self::toBool($row['scope_captcha_activated_required'] ?? null),
            infoForAppointment: self::emptyToNull($row['scope_info_for_appointment'] ?? null),
            infoForAllAppointments: self::emptyToNull($row['scope_info_for_all_appointments'] ?? null),
            slotsPerAppointment: $slotsPerAppointment,
            appointmentsPerMail: self::emptyToNull($row['scope_appointments_per_mail'] ?? null),
            whitelistedMails: self::emptyToNull($row['scope_whitelisted_mails'] ?? null),
            reservationDuration: self::nullableInt($row['scope_reservation_duration'] ?? null),
            activationDuration: self::nullableInt($row['scope_activation_duration'] ?? null),
            hint: $hint === '' ? null : $hint
        );
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $providerData
     */
    private function mapThinnedProvider(array $row, array $providerData): ThinnedProvider
    {
        $geoLat = $providerData['geo']['lat'] ?? null;
        $geoLon = $providerData['geo']['lon'] ?? null;
        $contactLat = $row['contact__lat'] ?? null;
        $contactLon = $row['contact__lon'] ?? null;

        return new ThinnedProvider(
            id: (int) ($row['id'] ?? 0),
            name: isset($row['name']) ? (string) $row['name'] : null,
            displayName: isset($row['display_name']) ? (string) $row['display_name'] : null,
            source: isset($row['source']) ? (string) $row['source'] : null,
            lon: isset($geoLon) ? (float) $geoLon : (isset($contactLon) ? (float) $contactLon : null),
            lat: isset($geoLat) ? (float) $geoLat : (isset($contactLat) ? (float) $contactLat : null),
            contact: new ThinnedContact(
                city: isset($row['contact__city']) ? (string) $row['contact__city'] : null,
                country: 'Germany',
                name: isset($row['name']) ? (string) $row['name'] : null,
                postalCode: isset($row['contact__postalCode']) ? (string) $row['contact__postalCode'] : null,
                region: isset($row['contact__region']) ? (string) $row['contact__region'] : null,
                street: isset($row['contact__street']) ? (string) $row['contact__street'] : null,
                streetNumber: isset($row['contact__streetNumber']) ? (string) $row['contact__streetNumber'] : null
            )
        );
    }

    /**
     * @param list<array<string, mixed>> $requestRows
     * @return array{0: ?int, 1: ?string, 2: int, 3: list<array{id: int, name: mixed, count: int}>}
     */
    private function mapRequests(array $requestRows): array
    {
        if ($requestRows === []) {
            return [0, null, 0, []];
        }

        $mainServiceId = $requestRows[0]['id'] ?? null;
        $mainServiceName = null;
        $mainServiceCount = 0;
        $subRequestCounts = [];

        foreach ($requestRows as $request) {
            $requestId = $request['id'] ?? null;
            if ($requestId === $mainServiceId) {
                $mainServiceCount++;
                if ($mainServiceName === null && isset($request['name'])) {
                    $mainServiceName = (string) $request['name'];
                }
                continue;
            }

            $key = (string) $requestId;
            if (!isset($subRequestCounts[$key])) {
                $subRequestCounts[$key] = [
                    'id' => (int) $requestId,
                    'name' => $request['name'] ?? null,
                    'count' => 0,
                ];
            }
            $subRequestCounts[$key]['count']++;
        }

        return [
            isset($mainServiceId) ? (int) $mainServiceId : 0,
            $mainServiceName,
            $mainServiceCount,
            array_values($subRequestCounts),
        ];
    }

    private function timestampFromDatetime(mixed $datetime): ?string
    {
        if ($datetime === null || $datetime === '') {
            return null;
        }
        $timestamp = strtotime((string) $datetime);
        if ($timestamp === false) {
            return null;
        }

        return (string) $timestamp;
    }

    private static function decodeJson(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }
        if (!is_string($value) || $value === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        return is_array($decoded) ? $decoded : [];
    }

    private static function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if ($value === null || $value === '') {
            return false;
        }
        if (is_numeric($value)) {
            return (int) $value === 1;
        }
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        return (int) $value;
    }

    private static function emptyToNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }
        $string = (string) $value;
        return $string === '' ? null : $string;
    }
}
