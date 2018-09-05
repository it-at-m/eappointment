<?php
namespace BO\Zmsentities\Collection;

class RequestRelationList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\RequestRelation';

    public function hasRequest($requestIdCsv)
    {
        $requestIdCsv = explode(',', $requestIdCsv);
        foreach ($requestIdCsv as $requestId) {
            if (!in_array($requestId, $this->getRequestList()->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function getRequestList()
    {
        $requestList = new RequestList();
        foreach ($this as $item) {
            if (isset($item['request'])) {
                $entity = new \BO\Zmsentities\Request($item['request']);
                $requestList->addEntity($entity);
            }
        }
        return $requestList;
    }

    public function toRequestRelation()
    {
        $collection = new self();
        foreach ($this as $entity) {
            $collection->addEntity($entity->toRequestRelation());
        }
        return $collection;
    }

    public function toProviderRelation()
    {
        $collection = new self();
        foreach ($this as $entity) {
            $collection->addEntity($entity->toProviderRelation());
        }
        return $collection;
    }
}
