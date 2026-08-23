<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository\Office;

use BO\Zmscitizenbackend\Models\Combinable;
use BO\Zmscitizenbackend\Models\Office;
use BO\Zmscitizenbackend\Models\OfficeServiceRelation;
use BO\Zmscitizenbackend\Models\Service;
use BO\Zmscitizenbackend\Models\ThinnedContact;
use BO\Zmscitizenbackend\Models\ThinnedProvider;
use BO\Zmscitizenbackend\Models\ThinnedScope;
use BO\Zmscitizenbackend\Models\Collections\OfficeList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceAndRelationList;
use BO\Zmscitizenbackend\Models\Collections\OfficeServiceRelationList;
use BO\Zmscitizenbackend\Models\Collections\ServiceList;

class OfficesServicesRelationsHydrator
{
    /**
     * @param list<array<string, mixed>> $officeRows
     * @param list<array<string, mixed>> $requestRows
     * @param list<array<string, mixed>> $relationRows
     */
    public function hydrate(
        array $officeRows,
        array $requestRows,
        array $relationRows,
        bool $showUnpublished = false
    ): OfficeServiceAndRelationList {
        return new OfficeServiceAndRelationList(
            $this->mapOffices($officeRows, $showUnpublished),
            $this->mapServices($requestRows, $relationRows, $showUnpublished),
            $this->mapRelations($relationRows, $showUnpublished)
        );
    }

    /**
     * @param list<array<string, mixed>> $officeRows
     */
    private function mapOffices(array $officeRows, bool $showUnpublished): OfficeList
    {
        $offices = [];
        $seenProviders = [];

        foreach ($officeRows as $row) {
            $providerKey = (string) ($row['source'] ?? '') . '_' . (string) ($row['id'] ?? '');
            if (isset($seenProviders[$providerKey])) {
                continue;
            }
            $seenProviders[$providerKey] = true;

            $data = self::decodeJson($row['data'] ?? null);
            if (self::shouldHideUnpublished($data, $showUnpublished)) {
                continue;
            }

            $offices[] = $this->officeFromRow($row, $data);
        }

        return new OfficeList($offices);
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $data
     */
    private function officeFromRow(array $row, array $data): Office
    {
        $scope = $this->mapScope($row, $data);
        $displayName = $row['display_name'] ?? null;
        $name = ($displayName !== null && $displayName !== '') ? $displayName : ($row['name'] ?? '');

        return new Office(
            id: (int) $row['id'],
            name: (string) $name,
            address: self::arrayValue($data, 'address'),
            showAlternativeLocations: self::boolValue($data, 'showAlternativeLocations'),
            displayNameAlternatives: self::arrayValue($data, 'displayNameAlternatives') ?? [],
            organization: self::stringValue($data, 'organization'),
            organizationUnit: self::stringValue($data, 'organizationUnit'),
            slotTimeInMinutes: isset($data['slotTimeInMinutes']) ? (int) $data['slotTimeInMinutes'] : null,
            geo: self::arrayValue($data, 'geo'),
            disabledByServices: self::arrayValue($data, 'dontShowByServices') ?? [],
            priority: isset($data['prio']) ? (int) $data['prio'] : 1,
            scope: $scope,
            slotsPerAppointment: $scope?->slotsPerAppointment,
            parentId: self::nullableInt($row['parent_id'] ?? null),
            allowDisabledServicesMix: self::intListOrNull($data['allowDisabledServicesMix'] ?? null),
            sharedBookingOfficeIds: self::intListOrNull($data['sharedBookingOfficeIds'] ?? null)
        );
    }

    /**
     * @param array<string, mixed> $row
     * @param array<string, mixed> $providerData
     */
    private function mapScope(array $row, array $providerData): ?ThinnedScope
    {
        if (!isset($row['scope_id']) || $row['scope_id'] === null || $row['scope_id'] === '') {
            return null;
        }

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
            id: (int) $row['id'],
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
     * @param list<array<string, mixed>> $relationRows
     */
    private function mapServices(array $requestRows, array $relationRows, bool $showUnpublished): ServiceList
    {
        $servicesProviderIds = $this->providerIdsByService($relationRows, $showUnpublished);
        $lookup = $this->requestLookup($requestRows);

        usort($requestRows, static function (array $left, array $right): int {
            return (string) ($left['id'] ?? '') <=> (string) ($right['id'] ?? '');
        });

        $services = [];
        foreach ($requestRows as $request) {
            $service = $this->serviceFromRow($request, $servicesProviderIds, $lookup, $showUnpublished);
            if ($service !== null) {
                $services[] = $service;
            }
        }

        return new ServiceList($services);
    }

    /**
     * @param list<array<string, mixed>> $relationRows
     * @return array<string, list<int>>
     */
    private function providerIdsByService(array $relationRows, bool $showUnpublished): array
    {
        $servicesProviderIds = [];
        foreach ($relationRows as $relation) {
            if (!$showUnpublished && !self::toBool($relation['public_visibility'] ?? true)) {
                continue;
            }
            $serviceId = (string) ($relation['request__id'] ?? '');
            if ($serviceId === '') {
                continue;
            }
            $servicesProviderIds[$serviceId] ??= [];
            $servicesProviderIds[$serviceId][] = (int) ($relation['provider__id'] ?? 0);
        }
        return $servicesProviderIds;
    }

    /**
     * @param list<array<string, mixed>> $requestRows
     * @return array<string, array<string, mixed>>
     */
    private function requestLookup(array $requestRows): array
    {
        $lookup = [];
        foreach ($requestRows as $request) {
            $lookup[(string) ($request['source'] ?? '') . ':' . (string) ($request['id'] ?? '')] = $request;
        }
        return $lookup;
    }

    /**
     * @param array<string, mixed> $request
     * @param array<string, list<int>> $servicesProviderIds
     * @param array<string, array<string, mixed>> $lookup
     */
    private function serviceFromRow(
        array $request,
        array $servicesProviderIds,
        array $lookup,
        bool $showUnpublished
    ): ?Service {
        $data = self::decodeJson($request['data'] ?? null);
        if (self::shouldHideUnpublished($data, $showUnpublished)) {
            return null;
        }

        $serviceId = (string) ($request['id'] ?? '');
        if ($serviceId === '' || empty($servicesProviderIds[$serviceId])) {
            return null;
        }

        $parentId = self::nullableInt($request['parent_id'] ?? ($data['parent_id'] ?? null));
        $variantId = self::nullableInt($request['variant_id'] ?? ($data['variant_id'] ?? null));

        return new Service(
            id: (int) $serviceId,
            name: (string) ($request['name'] ?? ''),
            maxQuantity: isset($data['maxQuantity']) ? (int) $data['maxQuantity'] : 1,
            combinable: new Combinable($this->combinableOffices($data, $serviceId, $servicesProviderIds)),
            parentId: $parentId,
            variantId: $variantId,
            showOnStartPage: isset($data['showOnStartPage']) ? (bool) $data['showOnStartPage'] : true,
            rootParentId: $this->resolveRootParentId($request, $lookup)
        );
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, list<int>> $servicesProviderIds
     * @return array<int|string, list<int>>
     */
    private function combinableOffices(array $data, string $serviceId, array $servicesProviderIds): array
    {
        $combinableData = $data['combinable'] ?? [];
        if (!is_array($combinableData)) {
            return [];
        }

        $serviceCombinations = [];
        foreach ($combinableData as $combinationServiceId) {
            $commonProviders = array_intersect(
                $servicesProviderIds[$serviceId] ?? [],
                $servicesProviderIds[(string) $combinationServiceId] ?? []
            );
            $serviceCombinations[$combinationServiceId] = array_values($commonProviders);
        }
        return $serviceCombinations;
    }

    /**
     * @param list<array<string, mixed>> $relationRows
     */
    private function mapRelations(array $relationRows, bool $showUnpublished): OfficeServiceRelationList
    {
        $relations = [];
        foreach ($relationRows as $relation) {
            if (!$showUnpublished && !self::toBool($relation['public_visibility'] ?? true)) {
                continue;
            }

            $maxQuantity = $relation['max_quantity'] ?? null;
            $relations[] = new OfficeServiceRelation(
                officeId: (int) ($relation['provider__id'] ?? 0),
                serviceId: (int) ($relation['request__id'] ?? 0),
                slots: (int) ($relation['slots'] ?? 0),
                public: self::toBool($relation['public_visibility'] ?? true),
                maxQuantity: ($maxQuantity === null || $maxQuantity === '') ? null : (int) $maxQuantity
            );
        }

        return new OfficeServiceRelationList($relations);
    }

    /**
     * @param array<string, mixed> $request
     * @param array<string, array<string, mixed>> $lookup
     */
    private function resolveRootParentId(array $request, array $lookup): int
    {
        $current = $request;
        $visited = [];

        while (true) {
            $parentId = $current['parent_id'] ?? null;
            if ($parentId === null || $parentId === '') {
                break;
            }
            $parentKey = (string) ($current['source'] ?? '') . ':' . (string) $parentId;
            if (isset($visited[$parentKey])) {
                break;
            }
            $visited[$parentKey] = true;
            if (!isset($lookup[$parentKey])) {
                return (int) $parentId;
            }
            $current = $lookup[$parentKey];
        }

        $parentId = $current['parent_id'] ?? null;
        if ($parentId !== null && $parentId !== '') {
            return (int) $parentId;
        }

        return (int) ($current['id'] ?? 0);
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function shouldHideUnpublished(array $data, bool $showUnpublished): bool
    {
        if ($showUnpublished || !isset($data['public'])) {
            return false;
        }
        return !$data['public'];
    }

    /**
     * @param array<string, mixed> $data
     * @return array<mixed>|null
     */
    private static function arrayValue(array $data, string $key): ?array
    {
        $value = $data[$key] ?? null;
        return is_array($value) ? $value : null;
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function boolValue(array $data, string $key): ?bool
    {
        if (!array_key_exists($key, $data)) {
            return null;
        }
        return (bool) $data[$key];
    }

    /**
     * @param array<string, mixed> $data
     */
    private static function stringValue(array $data, string $key): ?string
    {
        if (!isset($data[$key])) {
            return null;
        }
        return (string) $data[$key];
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

    private static function intListOrNull(mixed $value): ?array
    {
        if (!is_array($value)) {
            return null;
        }
        return array_map('intval', $value);
    }
}
