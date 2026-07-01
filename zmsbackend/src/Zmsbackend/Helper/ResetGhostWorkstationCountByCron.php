<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class ResetGhostWorkstationCountByCron
{
    /**
     * reset ghostWorkstationCount by cron
     *
     * @return void
     */

    public static function init()
    {
        $dateTime = new \DateTimeImmutable();
        $query = new \BO\Zmsbackend\Scope\Service\Scope();
        $scopeList = $query->readList();
        foreach ($scopeList as $scope) {
            $scope->setStatusQueue('ghostWorkstationCount', '-1');
            $query->updateGhostWorkstationCount($scope, $dateTime);
        }
    }
}
