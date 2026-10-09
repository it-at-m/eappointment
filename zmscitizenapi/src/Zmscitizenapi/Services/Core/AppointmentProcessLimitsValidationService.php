<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Services\Core;

use BO\Zmscitizenapi\Models\ThinnedProcess;
use BO\Zmscitizenapi\Utils\ErrorMessages;
use BO\Zmsentities\Process;

/**
 * Citizen-side replacement for the former zmsapi validateProcessLimits checks.
 * Keeps spa/maxQuantity enforcement off shared admin ProcessUpdate/Preconfirm/Confirm.
 */
class AppointmentProcessLimitsValidationService
{
    /** Must match {@see \BO\Zmsbackend\Slot\Service\Slot::MAX_SLOTS} / citizenview MAX_SLOTS */
    private const int MAX_SLOTS_PER_APPOINTMENT_FALLBACK = 25;

    private static function getError(string $key): array
    {
        return ErrorMessages::get($key);
    }

    /**
     * @param array<int|string, int> $serviceIdToCount
     */
    public static function validateAppointmentProcessLimits(
        ?int $slotCount,
        mixed $slotsPerAppointment,
        array $serviceIdToCount,
        int $officeId
    ): array {
        $errors = self::validateAppointmentSlotLimit($slotCount, $slotsPerAppointment)['errors'];
        if ($officeId > 0 && $serviceIdToCount !== []) {
            $errors = array_merge(
                $errors,
                self::validateServiceQuantityLimits(
                    $serviceIdToCount,
                    self::readMaxQuantityByServiceIdForOffice($officeId)
                )['errors']
            );
        }

        return ['errors' => $errors];
    }

    /**
     * Preconfirm/confirm only re-check spa. Service mix is fixed after reserve, and
     * maxQuantity needs a source/relation load that those steps otherwise skip.
     */
    public static function validateAppointmentLimitsForThinnedProcess(ThinnedProcess $process): array
    {
        return self::validateAppointmentSlotLimit(
            $process->slotCount,
            $process->scope?->getSlotsPerAppointment()
        );
    }

    /**
     * @param list<int|string> $serviceIds
     * @param list<int|string> $serviceCounts
     */
    public static function validateAppointmentLimitsForReserve(
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

        return self::validateAppointmentProcessLimits(
            $slotCount,
            $slotsPerAppointment,
            self::buildServiceCountMap($serviceIds, $serviceCounts),
            $officeId
        );
    }

    /**
     * @param list<int|string> $serviceIds
     * @param list<int|string> $serviceCounts
     * @return array<int, int>
     */
    public static function buildServiceCountMap(array $serviceIds, array $serviceCounts): array
    {
        $map = [];
        foreach ($serviceIds as $index => $serviceId) {
            $id = (int) $serviceId;
            $map[$id] = ($map[$id] ?? 0) + (int) ($serviceCounts[$index] ?? 0);
        }

        return $map;
    }

    /**
     * @return array<int, int>
     */
    public static function serviceCountMapFromThinnedProcess(ThinnedProcess $process): array
    {
        $map = [];
        if ($process->serviceId !== null && $process->serviceId > 0 && $process->serviceCount > 0) {
            $map[(int) $process->serviceId] = (int) $process->serviceCount;
        }
        foreach ($process->subRequestCounts as $subRequest) {
            if (!is_array($subRequest)) {
                continue;
            }
            $id = isset($subRequest['id']) ? (int) $subRequest['id'] : 0;
            $count = isset($subRequest['count']) ? (int) $subRequest['count'] : 0;
            if ($id > 0 && $count > 0) {
                $map[$id] = ($map[$id] ?? 0) + $count;
            }
        }

        return $map;
    }

    public static function validateAppointmentSlotLimit(?int $slotCount, mixed $slotsPerAppointment): array
    {
        if ($slotCount === null || $slotCount <= 0) {
            return ['errors' => []];
        }

        $maxSlots = self::resolveMaxSlotsPerAppointment($slotsPerAppointment);
        if ($slotCount > $maxSlots) {
            return ['errors' => [self::getError('tooManySlotsPerAppointment')]];
        }

        return ['errors' => []];
    }

    /**
     * @param array<int, int> $serviceIdToCount
     * @param array<int, int|null> $maxQuantityByServiceId
     */
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

    private static function resolveMaxSlotsPerAppointment(mixed $slotsPerAppointment): int
    {
        if ($slotsPerAppointment === null || $slotsPerAppointment === '') {
            return self::MAX_SLOTS_PER_APPOINTMENT_FALLBACK;
        }
        $parsed = (int) $slotsPerAppointment;
        if ($parsed < 1) {
            return self::MAX_SLOTS_PER_APPOINTMENT_FALLBACK;
        }

        return $parsed;
    }

    /**
     * @return array<int, int|null>
     */
    private static function readMaxQuantityByServiceIdForOffice(int $officeId): array
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
