<?php
namespace BO\Zmsentities\Collection;

class RequestList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Request';

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

    public function toSortedByGroup()
    {
        $list = array();
        foreach ($this as $entity) {
            if (! array_key_exists($entity->group, $list)) {
                $list[$entity->group] = new self();
            }
            $list[$entity->group]->addEntity($entity)->sortByName();
        }
        ksort($list);
        return $list;
    }

    /**
     * Filter the request list and return a new list with appropriate numbers of requests
     *
     * @param array $countlist - a list with the request.id as number and a count as value
     *
     * @return RequestList
     */
    public function withCountList($countList)
    {
        $requestList = new self();
        foreach ($countList as $requestId => $counter) {
            if (!$this->hasEntity($requestId)) {
                throw new \BO\Zmsentities\Exception\RequestListMissing(
                    "Requested item '$requestId' in RequestList is missing"
                );
            }
            while ($counter-- > 0) {
                $requestList[] = $this->getEntity($requestId);
            }
        }
        return $requestList;
    }

    public function hasAppointmentFromProviderData()
    {
        foreach ($this as $entity) {
            if ($entity->hasAppointmentFromProviderData()) {
                return true;
            }
        }
        return false;
    }

    public function withUniqueRequests()
    {
        $requestList = new self();
        foreach ($this as $request) {
            if (! $requestList->hasEntity($request->id)) {
                $requestList->addEntity(clone $request);
            }
        }
        return $requestList;
    }

    public function withDataAsObject()
    {
        $list = new self();
        foreach ($this as $request) {
            $request->data = ($request->data) ? json_decode($request->data) : null;
            $list->addEntity($request);
        }
        return $list;
    }
}
