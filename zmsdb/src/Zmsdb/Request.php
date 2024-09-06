<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;
use \BO\Zmsentities\Collection\RequestList as Collection;

/**
 *
 * @SuppressWarnings(TooManyPublicMethods)
 */
class Request extends Base
{
    public function readEntity($source, $requestId, $resolveReferences = 0)
    {
        $this->testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRequestSource($source)
            ->addConditionRequestId($requestId);
        $request = $this->fetchOne($query, new Entity());
        if (! $request->hasId()) {
            throw new Exception\Request\RequestNotFound("Could not find request with ID $source/$requestId");
        }
        return $request;
    }

    /**
     * @SuppressWarnings(Param)
     *
     */
    protected function readCollection($query)
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
            $collection = $this->readCollection($query);
        }
        return $collection;
    }

    public function readRequestsByIds($ids, $resolveReferences = 0)
    {
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addConditionIds($ids);
        $query->addEntityMapping();

        return $this->readCollection($query);
    }

    public function readRequestByArchiveId($archiveId, $resolveReferences = 0)
    {
        $collection = new Collection();
        if ($archiveId) {
            $query = new Query\Request(Query\Base::SELECT);
            $query->setResolveLevel($resolveReferences);
            $query->addConditionArchiveId($archiveId);
            $query->addEntityMapping();
            $collection = $this->readCollection($query);
        }
        return $collection;
    }

    public function readListByProvider($source, $providerId, $resolveReferences = 0)
    {
        $this->testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $requestRelationQuery = new RequestRelation();
        $query->setResolveLevel($resolveReferences);
        $query->addConditionProvider($providerId, $source);
        $query->addConditionRequestSource($source);
        $query->addEntityMapping();
        $requestList = $this->readCollection($query);
        foreach ($requestList as $request) {
            $requestRelation = $requestRelationQuery->readEntity($request->getId(), $providerId);
            $request['timeSlotCount'] = $requestRelation->getSlotCount();
        }
        return $requestList;
    }

    public function readListBySource($source, $resolveReferences = 0)
    {
        $this->testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addConditionRequestSource($source);
        $query->addEntityMapping();
        $requestList = $this->readCollection($query);
        return ($requestList->count()) ? $requestList->sortByCustomKey('id') : $requestList;
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

    public function writeEntity(Entity $entity)
    {
        $additionalData = ($entity->getAdditionalData()) ? json_encode($entity->getAdditionalData()) : '{}';
        $this->writeDeleteEntity($entity->getId(), $entity->getSource());
        $query = new Query\Request(Query\Base::INSERT);
        $query->addValues([
            'source' => $entity->getSource(),
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'group' => $entity->getGroup(),
            'link' =>  $entity->getLink(),
            'data' => $additionalData
        ]);
        $this->writeItem($query);
        return $this->readEntity($entity->getSource(), $entity->getId());
    }

    public function writeListBySource(\BO\Zmsentities\Source $source)
    {
        $this->writeDeleteListBySource($source->getSource());
        foreach ($source->getRequestList() as $request) {
            $this->writeEntity($request);
        }
        return $this->readListBySource($source->getSource());
    }

    public function writeImportEntity($request, $source = 'dldb')
    {
        $query = new Query\Request(Query\Base::REPLACE);
        $query->addValues([
            'source' => $source,
            'id' => $request['id'],
            'name' => $request['name'],
            'group' => (isset($request['group'])) ? $request['group'] : 'Sonstiges',
            'link' => ('dldb' == $source)
                ? 'https://service.berlin.de/dienstleistung/'. $request['id'] .'/'
                : ((isset($request['link'])) ? $request['link'] : ''),
            'data' => json_encode($request)
        ]);
        $this->writeItem($query);
        return $this->readEntity($source, $request['id']);
    }

    public function writeDeleteEntity($requestId, $source)
    {
        $query = new Query\Request(Query\Base::DELETE);
        $query->addConditionRequestId($requestId);
        $query->addConditionRequestSource($source);
        return $this->deleteItem($query);
    }

    public function writeDeleteListBySource($source)
    {
        $query = new Query\Request(Query\Base::DELETE);
        $query->addConditionRequestSource($source);
        return $this->deleteItem($query);
    }

    protected function testSource($source)
    {
        if (! (new Source())->readEntity($source)) {
            throw new Exception\Source\UnknownDataSource();
        }
    }

    public function readByIds($requestId)
    {
    }
}
