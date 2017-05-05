<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ResetGhostWorkstationCountByCron
{
    /**
     * reset ghostWorkstationCount by cron
     *
     * @return Entity
     */

    public static function init()
    {
        $dateTime = new \DateTimeImmutable();
        $query = new \BO\Zmsdb\Scope();
        $scopeList = $query->readList();
        foreach ($scopeList as $scope) {
            $scope->setStatusQueue('ghostWorkstationCount', '-1');
            $query->updateGhostWorkstationCount($scope, $dateTime);
        }
    }
}
