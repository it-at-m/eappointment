<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;
use \BO\Zmsentities\Collection\ScopeList as Collection;

class Scope extends Base
{
    public function readEntity($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $scope = $this->fetchOne($query, new Entity());
        $scope = $this->addDldbData($scope, $resolveReferences);
        return $scope;
    }

    public function readByClusterId($clusterId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionClusterId($clusterId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $scopeList->addEntity($entity);
            }
        }
        return $scopeList;
    }

    public function readByProviderId($providerId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $scopeList->addEntity($entity);
            }
        }
        return $scopeList;
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $scopeList->addEntity($entity);
            }
        }
        return $scopeList;
    }

    public function readList($resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        return $this->fetchList($query, new Entity());
    }

    /**
     * remove a scope
     *
     * @param
     * scopeId
     *
     * @return Resource Status
     */
    public function deleteEntity($scopeId)
    {
        $query =  new Query\Scope(Query\Base::DELETE);
        $query->addConditionScopeId($scopeId);
        return $this->deleteItem($query);
    }

    protected function addDldbData($scope, $resolveReferences)
    {
        if (isset($scope['provider'])) {
            if ($resolveReferences > 1 && $scope['provider']['source'] == 'dldb') {
                $scope['provider']['data'] = Helper\DldbData::readExtendedProviderData(
                    $scope['provider']['source'],
                    $scope['provider']['id']
                );
            }
        }
        return $scope;
    }
}
