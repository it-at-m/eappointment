<?php

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\Property;

class Calldisplay extends Schema\Entity
{
    public const PRIMARY = 'serverTime';

    public static string $schema = "calldisplay.json";

    /**
     * @return (Collection\ClusterList|Collection\ScopeList|Organisation|int)[]
     *
     * @psalm-return array{serverTime: int, clusters: Collection\ClusterList, scopes: Collection\ScopeList, organisation: Organisation}
     */
    public function getDefaults()
    {
        return [
            'serverTime' => (new \DateTime())->getTimestamp(),
            'clusters' => new Collection\ClusterList(),
            'scopes' => new Collection\ScopeList(),
            'organisation' => new Organisation(),
        ];
    }

    public function withResolvedCollections($input): static
    {
        $input =  (is_object($input)) ? $input->getArrayCopy() : $input;
        if (Property::__keyExists('scopelist', $input)) {
            $this->scopes = $this->getScopeListFromCsv($input['scopelist']);
        }
        if (Property::__keyExists('clusterlist', $input)) {
            $this->clusters = $this->getClusterListFromCsv($input['clusterlist']);
        }
        return $this;
    }

    public function hasScopeList(): bool
    {
        return (0 < $this->getScopeList()->count());
    }

    public function hasClusterList(): bool
    {
        return (0 < $this->getClusterList()->count());
    }

    public function setServerTime($timestamp): static
    {
        $this->serverTime = $timestamp;
        return $this;
    }

    public function getFullScopeList()
    {
        $scopeList = $this->getScopeList();
        foreach ($this->clusters as $cluster) {
            if (Property::__keyExists('scopes', $cluster)) {
                foreach ($cluster['scopes'] as $clusterScope) {
                    $scope = new Scope($clusterScope);
                    if (! $scopeList->hasEntity($scope['id'])) {
                        $scopeList->addEntity($scope);
                    }
                }
            }
        }
        return $scopeList;
    }

    public function getScopeList(): Collection\ScopeList
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

    public function getClusterList(): Collection\ClusterList
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

    public function getImageName(): string
    {
        $name = '';
        if (1 == $this->getScopeList()->count()) {
            $name = "s_" . $this->getScopeList()->getFirst()->id . "_bild";
        } elseif (1 == $this->getClusterList()->count()) {
            $name = "c_" . $this->getClusterList()->getFirst()->id . "_bild";
        }
        return $name;
    }

    public function withOutClusterDuplicates(): self
    {
        $calldisplay = new self($this);
        if ($calldisplay->hasClusterList() && $calldisplay->hasScopeList()) {
            $clusterScopeList = new Collection\ScopeList();
            foreach ($calldisplay->clusters as $cluster) {
                if (Property::__keyExists('scopes', $cluster)) {
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

    protected function getScopeListFromCsv($scopeIds = ''): Collection\ScopeList
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

    protected function getClusterListFromCsv($clusterIds = ''): Collection\ClusterList
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
