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
            Name=?
    ';

    const QUERY_LOGOUT = '
        UPDATE
            '. self::TABLE .'
        SET
            SessionID=""
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

    public function addConditionPassword($password)
    {
        $this->query->where('workstation.Passworthash', '=', md5($password));
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Workstation $entity)
    {
        $data = array();
        $data['aufrufzusatz'] = $entity->hint;
        $data['Kalenderansicht'] = $entity->getQueuePreference('appointmentsOnly', true);
        $data['clusteransicht'] = $entity->getQueuePreference('clusterEnabled', true);
        $data['StandortID'] = $entity->scope['id'];
        $data['BehoerdenID'] = current($entity->useraccount['departments']);
        $data['Arbeitsplatznr'] = $entity->name;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
