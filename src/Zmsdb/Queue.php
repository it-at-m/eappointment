<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Queue as Entity;

class Queue extends Base implements Interfaces\ResolveReferences
{
    /**
     * Read processList by scopeId and DateTime
     *
     * @param
     * scopeId
     * dateTime
     *
     * @return Collection processList
     */
    public function readListByScopeAndTime(
        $scopeId,
        \DateTimeInterface $dateTime,
        $resolveReferences = 0
    ) {
        $query = new Query\Queue(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionAssigned()
            ->addConditionTime($dateTime);
        $list = $this->fetchList($query, new Entity(), new \BO\Zmsentities\Collection\QueueList());
        return $list;
    }
}
