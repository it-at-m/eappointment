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
}
