<?php

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Helper\Sorter;
use BO\Zmsentities\Scope;

class ScopeList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Scope';

    protected $slotsByID = [];

    public function getAlternateRedirectUrl()
    {
        $scope = $this->getIterator()->current();
        return (1 == count($this) && $scope->getAlternateRedirectUrl()) ? $scope->getAlternateRedirectUrl() : null;
    }

    /**
    * Get shortest bookable start date of a scope in scopelist
    *
    * @return \DateTimeImmutable $date
    */
    public function getShortestBookableStart($now)
    {
        $date = $now;
        foreach ($this as $scope) {
            $startDate = $scope->getBookableStartDate($now);
            $date = ($startDate > $date) ? $startDate : $date;
        }
        return $date;
    }

    /**
    * Get longest bookable end date of a scope in scopelist
    *
    * @return \DateTimeImmutable $date
    */
    public function getGreatestBookableEnd($now)
    {
        $date = $now;
        foreach ($this as $scope) {
            $endDate = $scope->getBookableEndDate($now);
            $date = ($endDate > $date) ? $endDate : $date;
        }
        return $date;
    }

    /**
    * Get shortest bookable start date of a opened scope in scopelist
    *
    * @return \DateTimeImmutable $date, null
    */
    public function getShortestBookableStartOnOpenedScope($now)
    {
        $date = null;
        foreach ($this as $scope) {
            if ($scope->getStatus('availability', 'isOpened')) {
                $date = $now;
                $startDate = $scope->getBookableStartDate($date);
                $date = ($startDate > $date) ? $startDate : $date;
            }
        }
        return $date;
    }

    /**
    * Get longest bookable end date of a opened scope in scopelist
    *
    * @return \DateTimeImmutable $date
    */
    public function getGreatestBookableEndOnOpenedScope($now)
    {
        $date = null;
        foreach ($this as $scope) {
            if ($scope->getStatus('availability', 'isOpened')) {
                $date = $now;
                $endDate = $scope->getBookableEndDate($date);
                $date = ($endDate > $date) ? $endDate : $date;
            }
        }
        return $date;
    }

    public function withoutDublicates($scopeList = null)
    {
        $collection = new self();
        foreach ($this as $scope) {
            if (! $scopeList || ! $scopeList->hasEntity($scope->getId())) {
                $collection->addEntity(clone $scope);
            }
        }
        return $collection;
    }

    public function withUniqueScopes()
    {
        $scopeList = new self();
        foreach ($this as $scope) {
            if ($scope->getId() && ! $scopeList->hasEntity($scope->getId())) {
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

    public function withLessData(array $keepArray = [])
    {
        $scopeList = new self();
        foreach ($this as $scope) {
            $scopeList->addEntity(clone $scope->withLessData($keepArray));
        }
        return $scopeList;
    }

    public function sortByName()
    {
        $this->uasort(function ($a, $b) {
            $nameA = (isset($a->provider['name'])) ? $a->provider['name'] : $a->shortName;
            $nameB = (isset($b->provider['name'])) ? $b->provider['name'] : $b->shortName;
            return strcmp(
                Sorter::toSortableString(ucfirst($nameA)),
                Sorter::toSortableString(ucfirst($nameB))
            );
        });
        return $this;
    }

    public function withProviderID($source, $providerID)
    {
        $list = new ScopeList();
        foreach ($this as $scope) {
            if ($scope->provider['source'] == $source && $scope->provider['id'] == $providerID) {
                $list->addEntity(clone $scope);
            }
        }
        return $list;
    }

    public function addRequiredSlots($source, $providerID, $slotsRequired)
    {
        $scopeList = $this->withProviderID($source, $providerID);
        foreach ($scopeList as $scope) {
            if (!isset($this->slotsByID[$scope->id])) {
                $this->slotsByID[$scope->id] = 0;
            }
            $this->slotsByID[$scope->id] += $slotsRequired;
        }
        return $this;
    }

    public function getRequiredSlotsByScope(Scope $scope)
    {
        if (isset($this->slotsByID[$scope->id])) {
            return $this->slotsByID[$scope->id];
        }
        return 0;
    }

    public function hasOpenedScope()
    {
        $isOpened = false;
        foreach ($this as $entity) {
            if ($entity->getStatus('availability', 'isOpened')) {
                $isOpened = true;
            }
        }
        return $isOpened;
    }

    public function getProviderList()
    {
        $list = new ProviderList();
        foreach ($this as $scope) {
            $provider = $scope->getProvider();
            $list->addEntity($provider);
        }
        return $list->withUniqueProvider();
    }
}
