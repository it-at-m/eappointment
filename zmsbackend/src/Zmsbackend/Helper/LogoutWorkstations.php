<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class LogoutWorkstations
{
    /**
     * logout all workstations
     *
     * @return void
     */

    public static function init($verbose)
    {
        $query = new \BO\Zmsbackend\Workstation\Repository\Workstation(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences(1);
        $parameters = $query->getParameters();
        $connection = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        $statement = $connection->prepare("$query");
        $statement->execute($parameters);
        while ($data = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $dataEntity = new \BO\Zmsentities\Workstation();
            $dataEntity->exchangeArray($query->postProcessJoins($data));
            $dataEntity->setResolveLevel($query->getResolveLevel());
            if ($dataEntity->useraccount->lastLogin) {
                if ($verbose) {
                    \App::$log->info('Workstation logout', [
                        'workstationId' => $dataEntity->id,
                        'useraccountId' => $dataEntity->useraccount->id,
                    ]);
                }
                (new \BO\Zmsbackend\Workstation\Service\Workstation())->writeEntityLogoutByName($dataEntity->useraccount->id);
            }
        }
        \BO\Zmsbackend\Connection\Select::writeCommit();
    }
}
