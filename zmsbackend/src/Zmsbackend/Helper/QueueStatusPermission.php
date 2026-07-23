<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Helper;

use BO\Zmsentities\Collection\ProcessList;
use BO\Zmsentities\Collection\QueueList;
use BO\Zmsentities\Useraccount;

class QueueStatusPermission
{
    /**
     * Status → required queue permission (same map as UserQueue).
     */
    public const PERMISSION_BY_STATUS = [
        'preconfirmed' => 'waitingqueue',
        'confirmed' => 'waitingqueue',
        'queued' => 'waitingqueue',
        'reserved' => 'waitingqueue',
        'deleted' => 'waitingqueue',
        'called' => 'openqueue',
        'processing' => 'openqueue',
        'parked' => 'parkedqueue',
        'missed' => 'missedqueue',
        'finished' => 'finishedqueue',
    ];

    /**
     * Waiting-pipeline statuses also used by appointment planning screens that
     * share ProcessListByScopeAndDate / ProcessListByClusterAndDate.
     */
    private const APPOINTMENT_FALLBACK_STATUSES = [
        'preconfirmed',
        'confirmed',
        'queued',
        'reserved',
        'deleted',
    ];

    public static function getRequiredPermission(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return self::PERMISSION_BY_STATUS[$status] ?? null;
    }

    /**
     * @param bool $allowAppointmentFallback When true, users with `appointment`
     *        may see waiting-pipeline statuses even without `waitingqueue`.
     *        Used by process-by-date APIs (shared with calendar/appointments).
     *        UserQueue keeps this false (strict queue permissions only).
     */
    public static function isStatusAllowed(
        Useraccount $useraccount,
        ?string $status,
        bool $allowAppointmentFallback = false
    ): bool {
        $requiredPermission = self::getRequiredPermission($status);

        if ($requiredPermission === null) {
            return false;
        }

        if ($useraccount->hasPermissions([$requiredPermission])) {
            return true;
        }

        if (
            $allowAppointmentFallback
            && in_array($status, self::APPOINTMENT_FALLBACK_STATUSES, true)
            && $useraccount->hasPermissions(['appointment'])
        ) {
            return true;
        }

        return false;
    }

    public static function filterQueueList(
        QueueList $queueList,
        Useraccount $useraccount,
        bool $allowAppointmentFallback = false
    ): QueueList {
        $filtered = new QueueList();

        foreach ($queueList as $queue) {
            if (self::isStatusAllowed($useraccount, $queue->status ?? null, $allowAppointmentFallback)) {
                $filtered->addEntity($queue);
            }
        }

        return $filtered;
    }

    public static function filterProcessList(
        ProcessList $processList,
        Useraccount $useraccount,
        bool $allowAppointmentFallback = false
    ): ProcessList {
        $filtered = new ProcessList();

        foreach ($processList as $process) {
            $status = $process->getStatus();
            if (self::isStatusAllowed($useraccount, $status, $allowAppointmentFallback)) {
                $filtered->addEntity($process);
            }
        }

        return $filtered;
    }
}
