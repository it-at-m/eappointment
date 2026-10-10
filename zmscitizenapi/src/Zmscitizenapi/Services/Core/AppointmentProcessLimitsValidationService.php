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
        $slotsPerAppointment = $officeId > 0 ? self::readSlotsPerAppointmentByOffice($officeId) : null;
        $requiredSlotCount = $officeId > 0
            ? self::computeRequiredSlotCount($serviceIdToCount, self::readSlotsByOffice($officeId))
            : null;

        if ($requiredSlotCount !== null) {
            return self::validateProcessLimits(
                $requiredSlotCount,
                $slotsPerAppointment,
                $serviceIdToCount,
                $officeId
            );
        }

        // Missing relation or slots < 1: do not fall through to quantity-only checks.
        // Backend may otherwise normalize an unresolved requirement to a one-slot hold.
        if ($officeId > 0 && $serviceIdToCount !== []) {
            return ['errors' => [self::getError('invalidLocationAndServiceCombination')]];
        }

        return ['errors' => []];
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

    private static function readSlotsPerAppointmentByOffice(int $officeId): mixed
    {
        $scopeList = ZmsApiClientService::getScopes();
        if (!$scopeList) {
            return null;
        }

        $minSlots = null;
        foreach ($scopeList as $scope) {
            try {
                $provider = $scope->getProvider();
            } catch (\Throwable) {
                continue;
            }
            if (!$provider || (int) $provider->id !== $officeId) {
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
            $minSlots = $minSlots === null ? $parsed : min($minSlots, $parsed);
        }

        return $minSlots;
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
        $maxQuantityByServiceId = [];
        foreach (self::relationsForOffice($officeId) as $relation) {
            $serviceId = isset($relation->request->id) ? (int) $relation->request->id : 0;
            if ($serviceId <= 0) {
                continue;
            }
            $maxQuantity = $relation->getMaxQuantity();
            $maxQuantityByServiceId[$serviceId] = $maxQuantity === null || $maxQuantity === ''
                ? null
                : (int) $maxQuantity;
        }

        return $maxQuantityByServiceId;
    }

    private static function readSlotsByOffice(int $officeId): array
    {
        $slotsByServiceId = [];
        foreach (self::relationsForOffice($officeId) as $relation) {
            $serviceId = isset($relation->request->id) ? (int) $relation->request->id : 0;
            if ($serviceId <= 0) {
                continue;
            }
            $slotsByServiceId[$serviceId] = (int) $relation->slots;
        }

        return $slotsByServiceId;
    }

    private static function relationsForOffice(int $officeId): array
    {
        $relationList = ZmsApiClientService::getRequestRelationList();
        if (!$relationList) {
            return [];
        }

        $matches = [];
        foreach ($relationList as $relation) {
            $providerId = isset($relation->provider->id) ? (int) $relation->provider->id : 0;
            if ($providerId === $officeId) {
                $matches[] = $relation;
            }
        }

        return $matches;
    }
}
