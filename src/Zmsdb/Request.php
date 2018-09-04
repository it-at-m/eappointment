<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;
use \BO\Zmsentities\Collection\RequestList as Collection;

class Request extends Base
{
    public function readEntity($source, $requestId, $resolveReferences = 0)
    {
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRequestSource($source)
            ->addConditionRequestId($requestId);
        $request = $this->fetchOne($query, new Entity());
        if (!$request->hasId()) {
            throw new Exception\RequestNotFound("Could not find request with ID $source/$requestId");
        }
        return ($request->hasId()) ? $request : null;
    }

    /**
     * @SuppressWarnings(Param)
     *
     */
    protected function readCollection($query, $resolveReferences)
    {
        $requestList = new Collection();
        $statement = $this->fetchStatement($query);
        while ($requestData = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $request = new Entity($query->postProcessJoins($requestData));
            $requestList->addEntity($request);
        }
        return $requestList;
    }

    public function readRequestByProcessId($processId, $resolveReferences = 0)
    {
        $collection = new Collection();
        if ($processId) {
            $query = new Query\Request(Query\Base::SELECT);
            $query->setResolveLevel($resolveReferences);
            $query->addConditionProcessId($processId);
            $query->addEntityMapping();
            $collection = $this->readCollection($query, $resolveReferences);
        }
        return $collection;
    }

    public function readRequestByArchiveId($archiveId, $resolveReferences = 0)
    {
        $collection = new Collection();
        if ($archiveId) {
            $query = new Query\Request(Query\Base::SELECT);
            $query->setResolveLevel($resolveReferences);
            $query->addConditionArchiveId($archiveId);
            $query->addEntityMapping();
            $collection = $this->readCollection($query, $resolveReferences);
        }
        return $collection;
    }

    public function readListByProvider($source, $providerId, $resolveReferences = 0)
    {
        $query = new Query\Request(Query\Base::SELECT);
        $requestProviderQuery = new RequestProvider();
        $query->setResolveLevel($resolveReferences);
        $query->addConditionProviderId($providerId);
        $query->addConditionRequestSource($source);
        $query->addEntityMapping();
        $requestList = $this->readCollection($query, $resolveReferences);
        foreach ($requestList as $request) {
            $requestProvider = $requestProviderQuery->readEntity($request->getId(), $providerId);
            $request['timeSlotCount'] = $requestProvider->getSlotCount();
        }
        return $requestList;
    }

    public function readListBySource($source, $resolveReferences = 0)
    {
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addConditionRequestSource($source);
        $query->addEntityMapping();
        $requestList = $this->readCollection($query, $resolveReferences - 1);
        return $requestList;
    }

    public function readListByCluster(\BO\Zmsentities\Cluster $cluster, $resolveReferences = 0)
    {
        $intersectList = array();
        if ($cluster->scopes->count()) {
            foreach ($cluster->scopes as $scope) {
                $requestsByProvider = $this
                    ->readListByProvider(
                        $scope->provider['source'],
                        $scope->getProviderId(),
                        $resolveReferences - 1
                    )->getArrayCopy();
                if (count($requestsByProvider)) {
                    $intersectList = (count($intersectList)) ?
                        array_values(array_intersect($intersectList, $requestsByProvider)) : $requestsByProvider;
                }
            }
        }
        $requestList = new Collection($intersectList);
        return $requestList;
    }

    public function writeImportEntity($request, $topic = null, $source = 'dldb', $returnEntity = false)
    {
        $query = new Query\Request(Query\Base::REPLACE);
        $query->addValues([
            'source' => $source,
            'id' => $request['id'],
            'name' => $request['name'],
            'group' => (isset($topic['name'])) ? $topic['name'] : 'Sonstiges',
            'link' => ('dldb' == $source)
                ? 'https://service.berlin.de/dienstleistung/'. $request['id'] .'/'
                : ((isset($request['link'])) ? $request['link'] : ''),
            'data' => json_encode($request)
        ]);
        $this->writeItem($query);
        if ($returnEntity) {
            return $this->readEntity($source, $request['id']);
        }
    }
}
