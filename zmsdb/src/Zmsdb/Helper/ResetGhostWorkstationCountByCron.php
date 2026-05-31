<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ResetGhostWorkstationCountByCron
{
    /**
     * reset ghostWorkstationCount by cron
     */
    public static function init(): void
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
