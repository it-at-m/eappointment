<?php
namespace BO\Zmsentities\Collection;

class RequestList extends Base
{
    public function hasRequests($requestIdCsv)
    {
        $result = true;
        $requestIdCsv = explode(',', $requestIdCsv);
        foreach ($requestIdCsv as $requestId) {
            if (!in_array($requestId, $this->getIds())) {
                $result = false;
            }
        }
        return $result;
    }

    public function getIds()
    {
        $list = array();
        foreach ($this as $entity) {
            $list[] = $entity['id'];
        }
        return $list;
    }
}
