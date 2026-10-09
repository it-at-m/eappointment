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

    public static function validateReserveLimits(
        Process $process,
        array $serviceIds,
        array $serviceCounts,
        int $officeId
    ): array {
        $appointment = $process->getFirstAppointment();
        $slotCount = $appointment ? (int) $appointment->getSlotCount() : null;
        $slotsPerAppointment = null;
        if (
            isset($process->scope)
            && is_object($process->scope)
            && method_exists($process->scope, 'getSlotsPerAppointment')
        ) {
            $slotsPerAppointment = $process->scope->getSlotsPerAppointment();
        }

        return self::validateProcessLimits(
            $slotCount,
            $slotsPerAppointment,
            self::buildServiceCountMap($serviceIds, $serviceCounts),
            $officeId
        );
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
        $relationList = ZmsApiClientService::getRequestRelationList();
        if (!$relationList) {
            return $maxQuantityByServiceId;
        }

        foreach ($relationList as $relation) {
            $providerId = isset($relation->provider->id) ? (int) $relation->provider->id : 0;
            if ($providerId !== $officeId) {
                continue;
            }
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
}
