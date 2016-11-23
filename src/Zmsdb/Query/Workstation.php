<?php

namespace BO\Zmsdb\Query;

class Workstation extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'nutzer';

    const QUERY_LOGIN = '
        UPDATE
            '. self::TABLE .'
        SET
            SessionID=?,
            Datum=?
        WHERE
            Name=? AND
            Passworthash = ?
    ';

    const QUERY_LOGOUT = '
        UPDATE
            '. self::TABLE .'
        SET
            SessionID="",
            StandortID=0,
            BehoerdenID=0,
            Arbeitsplatznr="",
            aufrufzusatz=""
        WHERE
            Name=?
    ';

    public function getEntityMapping()
    {
        return [
            'id' => 'workstation.NutzerID',
            'hint' => 'workstation.aufrufzusatz',
            'name' => 'workstation.Arbeitsplatznr',
            'queue__appointmentsOnly' => 'workstation.Kalenderansicht',
            'queue__clusterEnabled' => 'workstation.clusteransicht',
            'scope__id' => 'workstation.StandortID'
        ];
    }

    public function addJoin()
    {
        $this->query->leftJoin(
            new Alias(Scope::TABLE, 'scope'),
            'workstation.StandortID',
            '=',
            'scope.StandortID'
        );
        $scopeQuery = new Scope($this->query, 'scope__');
        return [$scopeQuery];
    }

    public function getReferenceMapping()
    {
        return [
            'scope__$ref' => self::expression('CONCAT("/scope/", `workstation`.`StandortID`, "/")'),
        ];
    }

    public function addConditionLoginName($loginName)
    {
        $this->query->where('workstation.Name', '=', $loginName);
        return $this;
    }

    public function addConditionWorkstationId($workstationId)
    {
        $this->query->where('workstation.NutzerID', '=', $workstationId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('workstation.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionTime($now)
    {
        $this->query->where('workstation.Datum', '=', $now->format('Y-m-d'));
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Workstation $entity, $selectedDepartmentId = null)
    {
        $data = array();
        $data['aufrufzusatz'] = ('' == $entity->hint) ? $entity->name : $entity->hint;
        $data['Kalenderansicht'] = $entity->getQueuePreference('appointmentsOnly', true);
        $data['clusteransicht'] = $entity->getQueuePreference('clusterEnabled', true);
        $data['StandortID'] = $entity->scope['id'];
        $data['BehoerdenID'] = ($selectedDepartmentId) ? $entity->getDepartmentById($selectedDepartmentId) : 0;
        $data['Arbeitsplatznr'] = $entity->name;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
