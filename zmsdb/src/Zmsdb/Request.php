<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Request as Entity;
use BO\Zmsentities\Collection\RequestList as Collection;

/**
 *
 * @SuppressWarnings(TooManyPublicMethods)
 */
class Request extends Base
{
    public function readEntity($source, $requestId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "request-$source-$requestId-$resolveReferences";

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            return App::$cache->get($cacheKey);
        }

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

        if (App::$cache) {
            App::$cache->set($cacheKey, $request, \App::$SOURCE_CACHE_TTL);
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

    public function readListBySource($source, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "requestReadListBySource-$source-$resolveReferences";

        if (!$disableCache && App::$cache && App::$cache->has($cacheKey)) {
            return App::$cache->get($cacheKey);
        }

        $this->testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addConditionRequestSource($source);
        $query->addEntityMapping();
        $requestList = $this->readCollection($query);
        $requestList = ($requestList->count()) ? $requestList->sortByCustomKey('id') : $requestList;

        if (App::$cache) {
            App::$cache->set($cacheKey, $requestList, \App::$SOURCE_CACHE_TTL);
        }

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

    public function writeEntity(Entity $entity)
    {
        $additionalData = ($entity->getAdditionalData()) ? json_encode($entity->getAdditionalData()) : '{}';
        $this->writeDeleteEntity($entity->getId(), $entity->getSource());
        $query = new Query\Request(Query\Base::INSERT);
        $query->addValues([
            'source' => $entity->getSource(),
            'id' => $entity->getId(),
            'name' => $entity->getName(),
            'parent_id' => $entity->getParentId(),
            'group' => $entity->getGroup(),
            'link' =>  $entity->getLink(),
            'data' => $additionalData,
            'variant_id' => $entity->getVariantId()
        ]);
        $this->writeItem($query);

        $this->removeCache($entity);

        return $this->readEntity($entity->getSource(), $entity->getId());
    }

    public function writeListBySource(\BO\Zmsentities\Source $source)
    {
        $this->writeDeleteListBySource($source->getSource());
        foreach ($source->getRequestList() as $request) {
            $this->writeEntity($request);
            $this->removeCache($request);
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
                ? 'https://service.berlin.de/dienstleistung/' . $request['id'] . '/'
                : ((isset($request['link'])) ? $request['link'] : ''),
            'data' => json_encode($request)
        ]);
        $this->writeItem($query);
        return $this->readEntity($source, $request['id'], 0, true);
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

    public function removeCache(Entity $request)
    {
        if (!App::$cache) {
            return;
        }

        $source = $request->getSource();

        if (isset($request->id)) {
            $requestId = $request->getId();
            if (App::$cache->has("request-$source-$requestId-0")) {
                App::$cache->delete("request-$source-$requestId-0");
            }

            if (App::$cache->has("request-$source-$requestId-1")) {
                App::$cache->delete("request-$source-$requestId-1");
            }

            if (App::$cache->has("request-$source-$requestId-2")) {
                App::$cache->delete("request-$source-$requestId-2");
            }
        }

        if (App::$cache->has("requestReadListBySource-$source-0")) {
            App::$cache->delete("requestReadListBySource-$source-0");
        }

        if (App::$cache->has("requestReadListBySource-$source-1")) {
            App::$cache->delete("requestReadListBySource-$source-1");
        }

        if (App::$cache->has("requestReadListBySource-$source-2")) {
            App::$cache->delete("requestReadListBySource-$source-2");
        }
    }
}
