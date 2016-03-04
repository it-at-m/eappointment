<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;

class Request extends Base
{

    public function readEntity($source, $requestId)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Request(Query\Base::SELECT);
        $query->addEntityMapping()->addConditionRequestId($requestId);
        return $this->fetchOne($query, new Entity());
    }

    public function readSlotsOnEntity(\BO\Zmsentities\Request $entity)
    {
        $query = Query\Request::QUERY_SLOTS;
        $providerSlots = $this->getReader()->fetchAll($query, [
            'request_id' => $entity->id
        ]);
        return $providerSlots;
    }

    public function readRequestByProcessId($processId)
    {
        $requests = array();
        $query = Query\Request::QUERY_BY_PROCESSID;
        $result = $this->getReader()->fetchAll($query, [
            'process_id' => $processId,
        ]);
        if(count($result)){
            foreach($result as $request){
                $requests[] = $this->readEntity('dldb', $request['id']);
            }
        }
        return (count($requests)) ? $requests : null;
    }

    public function readList($source, $requestIds)
    {
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->addEntityMapping();
        if (null !== $requestIds) {
            $query
                ->addConditionRequestCsv($requestIds);
        }

        return $this->fetchList($query, new Entity());
    }

}
