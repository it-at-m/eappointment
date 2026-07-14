<?php

namespace BO\Zmsbackend\Queue\Service;

use BO\Zmsentities\Queue as Entity;

class Queue extends \BO\Zmsbackend\Base implements \BO\Zmsbackend\Interfaces\ResolveReferences
{
    /**
     * Read processList by scopeId and DateTime
     *
     * @param array|int $scopeIds
     * @param \DateTimeInterface $dateTime
     *
     * @return \BO\Zmsentities\Collection\QueueList
     */
    public function readListByScopeAndTime(
        $scopeIds,
        \DateTimeInterface $dateTime,
        $resolveReferences = 0
    ) {
        $query = new \BO\Zmsbackend\Queue\Repository\Queue(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeIds($scopeIds)
            ->addConditionAssigned()
            ->addConditionTime($dateTime);
        $list = $this->fetchList($query, new Entity(), new \BO\Zmsentities\Collection\QueueList());
        return $list;
    }
}
