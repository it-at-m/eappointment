<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;

class Request extends Base
{

    public function readEntity($source, $requestId, $resolveReferences = 0)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRequestId($requestId);
        $request = $this->fetchOne($query, new Entity());
        if ($resolveReferences >= 1 && $request['source'] == 'dldb') {
            $request['data'] = Helper\DldbData::readExtendedRequestData($source, $requestId);
        }
        return $request;
    }

    public function readSlotsOnEntity(\BO\Zmsentities\Request $entity)
    {
        $query = Query\Request::QUERY_SLOTS;
        $providerSlots = $this->getReader()->fetchAll(
            $query,
            ['request_id' => $entity->id]
        );
        return $providerSlots;
    }

    public function readRequestByProcessId($processId, $resolveReferences = 0)
    {
        $requests = array();
        $query = Query\Request::QUERY_BY_PROCESSID;
        $result = $this->getReader()->fetchAll(
            $query,
            ['process_id' => $processId,]
        );

        if (count($result)) {
            foreach ($result as $request) {
                $requests[] = $this->readEntity('dldb', $request['id'], $resolveReferences);
            }
        }
        return (count($requests)) ? $requests : null;
    }

    public function readProviderList($source, $requestIds, $resolveReferences = 0)
    {
        if ('dldb' !== $source) {
            return [];
        }
        $requestIds = \explode(',', $requestIds);
        $providerIds = array();
        foreach ($requestIds as $requestId) {
            $request = $this->readEntity($source, $requestId, 2);
            $providerIds[$requestId] = $request->getProviderIds();
        }
        return $providerIds;
    }
}
