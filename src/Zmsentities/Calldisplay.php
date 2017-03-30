<?php

namespace BO\Zmsentities;

class Calldisplay extends Schema\Entity
{
    const PRIMARY = 'serverTime';

    public static $schema = "calldisplay.json";

    public function getDefaults()
    {
        return [
            'serverTime' => (new \DateTime())->getTimestamp(),
            'clusters' => new Collection\ClusterList(),
            'scopes' => new Collection\ScopeList(),
        ];
    }

    public function withResolvedCollections($input)
    {
        if (array_key_exists('scopelist', $input)) {
            $this->scopes = $this->getScopeListFromCsv($input['scopelist']);
        }
        if (array_key_exists('clusterlist', $input)) {
            $this->clusters = $this->getClusterListFromCsv($input['clusterlist']);
        }
        return $this;
    }

    public function hasScopeList()
    {
        return $this->toProperty()->scopes->isAvailable();
    }

    public function hasClusterList()
    {
        return $this->toProperty()->clusters->isAvailable();
    }

    public function setServerTime($timestamp)
    {
        $this->serverTime = $timestamp;
        return $this;
    }

    public function getScopeList()
    {
        if (!$this->scopes instanceof Collection\ScopeList) {
            $scopeList = new Collection\ScopeList();
            foreach ($this->scopes as $scope) {
                $scopeList->addEntity(new Scope($scope));
            }
            $this->scopes = $scopeList;
        }
        return $this->scopes;
    }

    public function getClusterList()
    {
        if (!$this->clusters instanceof Collection\ClusterList) {
            $clusterList = new Collection\ClusterList();
            foreach ($this->clusters as $cluster) {
                $clusterList->addEntity(new Cluster($cluster));
            }
            $this->clusters = $clusterList;
        }
        return $this->clusters;
    }

    public function getImageName()
    {
        $name = '';
        if (1 == $this->getScopeList()->count()) {
            $name = "s_" . $this->getScopeList()->getFirst()->id . "_bild";
        } elseif (1 == $this->getClusterList()->count()) {
            $name = "c_" . $this->getClusterList()->getFirst()->id . "_bild";
        }
        return $name;
    }

    public function withOutClusterDuplicates()
    {
        $calldisplay = new self($this);
        if ($calldisplay->hasClusterList() && $calldisplay->hasScopeList()) {
            $clusterScopeList = new Collection\ScopeList();
            foreach ($calldisplay->clusters as $cluster) {
                if (array_key_exists('scopes', $cluster)) {
                    foreach ($cluster['scopes'] as $clusterScope) {
                        $scope = new Scope($clusterScope);
                        $clusterScopeList->addEntity($scope);
                    }
                }
            }
            $scopeList = new Collection\ScopeList();
            foreach ($calldisplay->scopes as $scope) {
                if (! $clusterScopeList->hasEntity($scope['id'])) {
                    $scope = new Scope($scope);
                    $scopeList->addEntity($scope);
                }
            }
            $calldisplay->scopes = $scopeList;
        }
        return $calldisplay;
    }

    protected function getScopeListFromCsv($scopeIds = '')
    {
        $scopeList = new Collection\ScopeList();
        $scopeIds = explode(',', $scopeIds);
        if ($scopeIds) {
            foreach ($scopeIds as $scopeId) {
                $scope = new Scope(array('id' => $scopeId));
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList;
    }

    protected function getClusterListFromCsv($clusterIds = '')
    {
        $clusterList = new Collection\ClusterList();
        $clusterIds = explode(',', $clusterIds);
        if ($clusterIds) {
            foreach ($clusterIds as $clusterId) {
                $cluster = new Cluster(array('id' => $clusterId));
                $clusterList->addEntity($cluster);
            }
        }
        return $clusterList;
    }
}
