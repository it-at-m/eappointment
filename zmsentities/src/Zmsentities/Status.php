<?php

namespace BO\Zmsentities;

class Status extends Schema\Entity
{
    public const PRIMARY = 'version';
    public static ?string $schema = "status.json";

    /**
     * @return (float|int|string)[][]
     *
     * @psalm-return array{database: array{nodeConnections: float, clusterStatus: 'OFF', logbin: 'OFF'}, processes: array{blocked: 0, called: 0, confirmed: 0, preconfirmed: 0, deleted: 0, missed: 0, parked: 0, reserved: 0, withExternalUserId: 0, confirmedWithExternalUserId: 0, lastInsert: 0}, mail: array{queueCount: 0, oldestSeconds: 0, newestSeconds: 0}}
     */
    public function getDefaults()
    {
        return [
            'database' => array (
                'nodeConnections' => 0.0,
                'clusterStatus' => 'OFF',
                'logbin' => 'OFF',
            ),
            'processes' => array (
                'blocked' => 0,
                'called' => 0,
                'confirmed' => 0,
                'preconfirmed' => 0,
                'deleted' => 0,
                'missed' => 0,
                'parked' => 0,
                'reserved' => 0,
                'withExternalUserId' => 0,
                'confirmedWithExternalUserId' => 0,
                'lastInsert' => 0,
            ),
            'mail' => array (
                'queueCount' => 0,
                'oldestSeconds' => 0,
                'newestSeconds' => 0,
            )
        ];
    }
}
