<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmsentities\Process;

class AppointmentProcessLimitsValidationService
{
    /** Must match Slot::MAX_SLOTS / citizenview MAX_SLOTS */
    private const int MAX_SLOTS_FALLBACK = 25;

    private static function getError(string $key): array
    {
        return ErrorMessages::get($key);
    }

    public static function validateProcessLimits(
        ?int $slotCount,
        mixed $slotsPerAppointment,
        array $serviceIdToCount,
        int $officeId
    ): array {
        $errors = self::validateSlotLimit($slotCount, $slotsPerAppointment)['errors'];
        if ($officeId > 0 && $serviceIdToCount !== []) {
            $errors = array_merge(
                $errors,
                self::validateServiceQuantityLimits(
                    $serviceIdToCount,
                    self::readMaxQuantityByOffice($officeId)
                )['errors']
            );
        }

        return ['errors' => $errors];
    }

    public static function validateThinnedProcessLimits(ThinnedProcess $process): array
    {
        return self::validateSlotLimit(
            $process->slotCount,
            $process->scope?->getSlotsPerAppointment()
        );
    }

    /**
     * Request-side check before free-slot lookup. Uses relation slots × counts so over-spa
     * rejects with tooManySlotsPerAppointment even when no free hole of that size exists.
     */
    public static function validateReserveRequestLimits(
        array $serviceIds,
        array $serviceCounts,
        int $officeId
    ): array {
        $serviceIdToCount = self::buildServiceCountMap($serviceIds, $serviceCounts);
        if ($officeId <= 0) {
            return ['errors' => []];
        }

        $relationMaps = self::relationMapsForOffice($officeId);

        return self::validateRequestLimitsWithMaps(
            $serviceIdToCount,
            self::computeRequiredSlotCount($serviceIdToCount, $relationMaps['slots']),
            self::readSlotsPerAppointmentByOffice($officeId),
            $relationMaps['maxQuantity']
        );
    }

    /**
     * Keep offices that pass the same request-side slot/quantity limits as reserve.
     * Loads scopes and relations once for the whole office list (calendar multi-office path).
     *
     * @param list<string|int> $officeIds
     * @param list<string|int> $serviceIds
     * @param list<string|int> $serviceCounts
     * @return array{officeIds?: list<string>, errors?: list<array>}
     */
    public static function filterOfficeIdsWithinRequestLimits(
        array $officeIds,
        array $serviceIds,
        array $serviceCounts
    ): array {
        $serviceIdToCount = self::buildServiceCountMap($serviceIds, $serviceCounts);
        $slotsPerAppointmentByOffice = self::indexSlotsPerAppointmentByOffice();
        $relationMapsByOffice = self::indexRelationMapsByOffice();

        $eligible = [];
        $lastErrors = ['errors' => []];

        foreach ($officeIds as $officeIdRaw) {
            $officeId = (int) $officeIdRaw;
            if ($officeId <= 0) {
                continue;
            }

            $relationMaps = $relationMapsByOffice[$officeId] ?? ['slots' => [], 'maxQuantity' => []];
            $limitErrors = self::validateRequestLimitsWithMaps(
                $serviceIdToCount,
                self::computeRequiredSlotCount($serviceIdToCount, $relationMaps['slots']),
                $slotsPerAppointmentByOffice[$officeId] ?? null,
                $relationMaps['maxQuantity']
            );
            if (!empty($limitErrors['errors'])) {
                $lastErrors = $limitErrors;
                continue;
            }

            $eligible[] = (string) $officeIdRaw;
        }

        if ($eligible === []) {
            return $lastErrors;
        }

        return ['officeIds' => $eligible];
    }

    public static function validateReserveLimits(
        Process $process,
        array $serviceIds,
        array $serviceCounts,
        int $officeId
    ): array {
        $appointment = $process->getFirstAppointment();
        $slotCount = $appointment ? (int) $appointment->getSlotCount() : null;
        $slotsPerAppointment = self::readSlotsPerAppointmentFromProcess($process);
        // Free-process payloads often omit client.slotsPerAppointment; fall back to the office scope.
        if (($slotsPerAppointment === null || $slotsPerAppointment === '') && $officeId > 0) {
            $slotsPerAppointment = self::readSlotsPerAppointmentByOffice($officeId);
        }

        return self::validateProcessLimits(
            $slotCount,
            $slotsPerAppointment,
            self::buildServiceCountMap($serviceIds, $serviceCounts),
            $officeId
        );
    }

    public static function computeRequiredSlotCount(array $serviceIdToCount, array $slotsByServiceId): ?int
    {
        if ($serviceIdToCount === []) {
            return null;
        }

        $total = 0;
        foreach ($serviceIdToCount as $serviceId => $count) {
            if ($count <= 0) {
                continue;
            }
            $sid = (int) $serviceId;
            if (!array_key_exists($sid, $slotsByServiceId)) {
                return null;
            }
            $slots = (int) $slotsByServiceId[$sid];
            if ($slots < 1) {
                return null;
            }
            $total += $slots * (int) $count;
        }

        return $total > 0 ? $total : null;
    }

    private static function readSlotsPerAppointmentFromProcess(Process $process): mixed
    {
        if (
            !isset($process->scope)
            || !is_object($process->scope)
            || !method_exists($process->scope, 'getSlotsPerAppointment')
        ) {
            return null;
        }

        return $process->scope->getSlotsPerAppointment();
    }

    /**
     * Shared request-side checks for reserve and calendar filtering (indexed maps already loaded).
     * Slot and quantity errors are both returned when both limits fail.
     *
     * @param array<int, int> $serviceIdToCount
     * @param array<int, int|null> $maxQuantityByServiceId
     * @return array{errors: list<array>}
     */
    private static function validateRequestLimitsWithMaps(
        array $serviceIdToCount,
        ?int $requiredSlotCount,
        mixed $slotsPerAppointment,
        array $maxQuantityByServiceId
    ): array {
        if ($requiredSlotCount === null) {
            // Missing relation or slots < 1: do not fall through to quantity-only checks.
            // Backend may otherwise normalize an unresolved requirement to a one-slot hold.
            if ($serviceIdToCount !== []) {
                return ['errors' => [self::getError('invalidLocationAndServiceCombination')]];
            }

            return ['errors' => []];
        }

        return [
            'errors' => array_merge(
                self::validateSlotLimit($requiredSlotCount, $slotsPerAppointment)['errors'],
                self::validateServiceQuantityLimits(
                    $serviceIdToCount,
                    $maxQuantityByServiceId
                )['errors']
            ),
        ];
    }

    private static function readSlotsPerAppointmentByOffice(int $officeId): mixed
    {
        return self::indexSlotsPerAppointmentByOffice()[$officeId] ?? null;
    }

    /**
     * @return array<int, int> officeId => minimum slotsPerAppointment across scopes
     */
    private static function indexSlotsPerAppointmentByOffice(): array
    {
        $scopeList = ZmsApiClientService::getScopes();
        if (!$scopeList) {
            return [];
        }

        $byOffice = [];
        foreach ($scopeList as $scope) {
            try {
                $provider = $scope->getProvider();
            } catch (\Throwable) {
                continue;
            }
            if (!$provider) {
                continue;
            }
            $officeId = (int) $provider->id;
            if ($officeId <= 0) {
                continue;
            }
            $slotsPerAppointment = $scope->getSlotsPerAppointment();
            if ($slotsPerAppointment === null || $slotsPerAppointment === '') {
                continue;
            }
            $parsed = (int) $slotsPerAppointment;
            if ($parsed < 1) {
                continue;
            }
            $byOffice[$officeId] = isset($byOffice[$officeId])
                ? min($byOffice[$officeId], $parsed)
                : $parsed;
        }

        return $byOffice;
    }

    public static function buildServiceCountMap(array $serviceIds, array $serviceCounts): array
    {
        $map = [];
        foreach ($serviceIds as $index => $serviceId) {
            $id = (int) $serviceId;
            $map[$id] = ($map[$id] ?? 0) + (int) ($serviceCounts[$index] ?? 0);
        }

        return $map;
    }

    public static function validateSlotLimit(?int $slotCount, mixed $slotsPerAppointment): array
    {
        if ($slotCount === null || $slotCount <= 0) {
            return ['errors' => [self::getError('processInvalid')]];
        }

        $maxSlots = self::resolveMaxSlots($slotsPerAppointment);
        if ($slotCount > $maxSlots) {
            return ['errors' => [self::getError('tooManySlotsPerAppointment')]];
        }

        return ['errors' => []];
    }

    public static function validateServiceQuantityLimits(array $serviceIdToCount, array $maxQuantityByServiceId): array
    {
        foreach ($serviceIdToCount as $serviceId => $count) {
            if ($count <= 0) {
                continue;
            }
            $maxQuantity = $maxQuantityByServiceId[(int) $serviceId] ?? null;
            if ($maxQuantity !== null && $maxQuantity > 0 && $count > $maxQuantity) {
                return ['errors' => [self::getError('tooManyServicesPerAppointment')]];
            }
        }

        return ['errors' => []];
    }

    private static function resolveMaxSlots(mixed $slotsPerAppointment): int
    {
        if ($slotsPerAppointment === null || $slotsPerAppointment === '') {
            return self::MAX_SLOTS_FALLBACK;
        }
        $parsed = (int) $slotsPerAppointment;
        if ($parsed < 1) {
            return self::MAX_SLOTS_FALLBACK;
        }

        return $parsed;
    }

    private static function readMaxQuantityByOffice(int $officeId): array
    {
        return self::relationMapsForOffice($officeId)['maxQuantity'];
    }

    /**
     * @return array{slots: array<int, int>, maxQuantity: array<int, int|null>}
     */
    private static function relationMapsForOffice(int $officeId): array
    {
        return self::indexRelationMapsByOffice()[$officeId]
            ?? ['slots' => [], 'maxQuantity' => []];
    }

    /**
     * @return array<int, array{slots: array<int, int>, maxQuantity: array<int, int|null>}>
     */
    private static function indexRelationMapsByOffice(): array
    {
        $relationList = ZmsApiClientService::getRequestRelationList();
        if (!$relationList) {
            return [];
        }

        $byOffice = [];
        foreach ($relationList as $relation) {
            $officeId = isset($relation->provider->id) ? (int) $relation->provider->id : 0;
            $serviceId = isset($relation->request->id) ? (int) $relation->request->id : 0;
            if ($officeId <= 0 || $serviceId <= 0) {
                continue;
            }
            if (!isset($byOffice[$officeId])) {
                $byOffice[$officeId] = ['slots' => [], 'maxQuantity' => []];
            }
            $byOffice[$officeId]['slots'][$serviceId] = (int) $relation->slots;
            $maxQuantity = $relation->getMaxQuantity();
            $byOffice[$officeId]['maxQuantity'][$serviceId] = $maxQuantity === null || $maxQuantity === ''
                ? null
                : (int) $maxQuantity;
        }

        return $byOffice;
    }
}
