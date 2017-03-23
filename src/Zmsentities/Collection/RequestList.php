<?php
namespace BO\Zmsentities\Collection;

class RequestList extends Base
{
    public function hasRequests($requestIdCsv)
    {
        $requestIdCsv = explode(',', $requestIdCsv);
        foreach ($requestIdCsv as $requestId) {
            if (!in_array($requestId, $this->getIds())) {
                return false;
            }
        }
        return true;
    }

    public function getIds()
    {
        $idList = array();
        foreach ($this as $request) {
            $idList[] = $request['id'];
        }
        return $idList;
    }

    public function getCSV()
    {
        return implode(',', $this->getIds());
    }

    public function toSortedByGroup()
    {
        $list = array();
        foreach ($this as $entity) {
            $list[$entity->group][] = $entity;
        }
        ksort($list);
        return $list;
    }
}
