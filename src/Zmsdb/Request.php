<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;
use \BO\Zmsentities\Collection\RequestList as Collection;

class Request extends Base
{
    public function readEntity($source, $requestId, $resolveReferences = 0)
    {
        self::testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->setResolveLevel($resolveReferences)
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionRequestId($requestId);
        $request = $this->fetchOne($query, new Entity());
        if (!$request->hasId()) {
            throw new Exception\RequestNotFound("Could not find request with ID $source/$requestId");
        }
        return ($request->hasId()) ? $request : null;
    }

    public function readSlotsOnEntity(\BO\Zmsentities\Request $entity)
    {
        $query = Query\Request::getQuerySlots();
        $providerSlots = $this->getReader()->fetchAll(
            $query,
            ['request_id' => $entity->id]
        );
        return $providerSlots;
    }

    protected static function testSource($source)
    {
        if ('dldb' !== $source) {
            throw new Exception\UnknownDataSource("Unknown source ". htmlspecialchars($source));
        }
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
            $request = new Entity($query->postProcess($requestData));
            $requestList->addEntity($request);
        }
        return $requestList;
    }

    public function readRequestByProcessId($processId, $resolveReferences = 0)
    {
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addEntityMapping();
        $query->addConditionProcessId($processId);
        return $this->readCollection($query, $resolveReferences);
    }

    public function readListByProvider($source, $providerId, $resolveReferences = 0)
    {
        self::testSource($source);
        $query = new Query\Request(Query\Base::SELECT);
        $query->setResolveLevel($resolveReferences);
        $query->addEntityMapping();
        $query->addConditionProviderId($providerId);
        return $this->readCollection($query, $resolveReferences);
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
                $intersectList = (count($intersectList)) ?
                    array_values(array_intersect($intersectList, $requestsByProvider)) : $requestsByProvider;
            }
        }
        $requestList = new Collection($intersectList);
        return $requestList;
    }
}
