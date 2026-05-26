<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class LogoutWorkstations
{
    /**
     * logout all workstations
     *
     * @return Entity
     */

    public static function init($verbose)
    {
        $query = new \BO\Zmsdb\Query\Workstation(\BO\Zmsdb\Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences(1);
        $parameters = $query->getParameters();
        $connection = \BO\Zmsdb\Connection\Select::getWriteConnection();
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
                (new \BO\Zmsdb\Workstation())->writeEntityLogoutByName($dataEntity->useraccount->id);
            }
        }
        \BO\Zmsdb\Connection\Select::writeCommit();
    }
}
