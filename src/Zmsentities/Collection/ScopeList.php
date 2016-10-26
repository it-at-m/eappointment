<?php
namespace BO\Zmsentities\Collection;

class ScopeList extends Base
{
    public function getAlternateRedirectUrl()
    {
        $result = null;
        $scope = reset($this);
        $alternateUrl = $scope->toProperty()->preferences->client->alternateAppointmentUrl->get();
        if (1 == count($this) && $alternateUrl) {
            $result = $alternateUrl;
        }
        return $result;
    }

    public function withUniqueScopes()
    {
        $scopeList = new self();
        foreach ($this as $scope) {
            if (! $scopeList->hasEntity($scope->id)) {
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList;
    }

    public function addScopeList(Collection $scopeList)
    {
        foreach ($scopeList as $scope) {
            $this->addEntity($scope);
        }
        return $this;
    }
}
