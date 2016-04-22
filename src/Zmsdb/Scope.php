<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;

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
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionClusterId($clusterId);
        return $this->fetchList($query, new Entity());
    }

    public function readByProviderId($providerId, $resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId);
        return $this->fetchList($query, new Entity());
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        return $this->fetchList($query, new Entity());
    }

    public function readList($resolveReferences = 0)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        return $this->fetchList($query, new Entity());
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
