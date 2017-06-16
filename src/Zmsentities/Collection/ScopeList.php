<?php
namespace BO\Zmsentities\Collection;

class ScopeList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Scope';

    public function getAlternateRedirectUrl()
    {
        $scope = reset($this);
        return (1 == count($this) && $scope->getAlternateRedirectUrl()) ? $scope->getAlternateRedirectUrl() : null;
    }

    public function withoutDublicates($scopeList)
    {
        $collection = new self();
        foreach ($this as $scope) {
            if (! $scopeList->hasEntity($scope->id)) {
                $collection->addEntity(clone $scope);
            }
        }
        return $collection;
    }

    public function withUniqueScopes()
    {
        $scopeList = new self();
        foreach ($this as $scope) {
            if (! $scopeList->hasEntity($scope->id)) {
                $scopeList->addEntity(clone $scope);
            }
        }
        return $scopeList;
    }

    public function addScopeList($scopeList)
    {
        foreach ($scopeList as $scope) {
            $this->addEntity($scope);
        }
        return $this;
    }

    public function withLessData()
    {
        $scopeList = new self();
        foreach ($this as $scope) {
            $scopeList->addEntity(clone $scope->withLessData());
        }
        return $scopeList;
    }

    public function sortByContactName()
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString(ucfirst($a->contact['name'])),
                Sorter::toSortableString(ucfirst($b->contact['name']))
            );
        });
        return $this;
    }
}
