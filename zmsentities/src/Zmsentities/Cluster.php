<?php

namespace BO\Zmsentities;

class Cluster extends Schema\Entity implements Useraccount\AccessInterface
{
    public const PRIMARY = 'id';

    public static $schema = "cluster.json";

    public function getDefaults()
    {
        return [
            //'name' => '',
            'scopes' => new Collection\ScopeList(),
        ];
    }

    public function getName()
    {
        return $this->toProperty()->name->get();
    }

    public function getScopesWorkstationCount()
    {
        $workstationCount = 0;
        if ($this->toProperty()->scopes->get()) {
            foreach ($this->scopes as $scope) {
                $entity = new Scope($scope);
                $workstationCount += $entity->status['queue']['workstationCount'];
            }
        }
        return $workstationCount;
    }

    public function hasAccess(Useraccount $useraccount)
    {
        if ($useraccount->hasPermissions(['superuser'])) {
            return true;
        }

        foreach ($this->scopes as $scope) {
            $scopeEntity = $scope instanceof Scope ? $scope : new Scope($scope);

            if ($useraccount->hasScope($scopeEntity->id)) {
                return true;
            }
        }

        return false;
    }
}
