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
     * No appointment fallback: appointment alone must not expose waiting queue data.
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

    public static function getRequiredPermission(?string $status): ?string
    {
        if ($status === null || $status === '') {
            return null;
        }

        return self::PERMISSION_BY_STATUS[$status] ?? null;
    }

    public static function isStatusAllowed(Useraccount $useraccount, ?string $status): bool
    {
        $requiredPermission = self::getRequiredPermission($status);

        if ($requiredPermission === null) {
            return false;
        }

        return $useraccount->hasPermissions([$requiredPermission]);
    }

    public static function filterQueueList(QueueList $queueList, Useraccount $useraccount): QueueList
    {
        $filtered = new QueueList();

        foreach ($queueList as $queue) {
            if (self::isStatusAllowed($useraccount, $queue->status ?? null)) {
                $filtered->addEntity($queue);
            }
        }

        return $filtered;
    }

    public static function filterProcessList(ProcessList $processList, Useraccount $useraccount): ProcessList
    {
        $filtered = new ProcessList();

        foreach ($processList as $process) {
            if (self::isStatusAllowed($useraccount, $process->getStatus())) {
                $filtered->addEntity($process);
            }
        }

        return $filtered;
    }
}
