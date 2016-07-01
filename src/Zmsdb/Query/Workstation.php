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

    public function getEntityMapping()
    {
        return [
            'id' => 'workstation.NutzerID',
            'hint' => 'workstation.aufrufzusatz',
            'name' => 'workstation.Arbeitsplatznr',
            'queue__appointmentsOnly' => 'workstation.Kalenderansicht',
            'queue__clusterEnabled' => 'workstation.clusteransicht',
            'scope__id' => 'workstation.StandortID',
        ];
    }

    public function addConditionLoginName($loginName)
    {
        $this->query->where('workstation.Name', '=', $loginName);
        return $this;
    }

    public function addConditionPassword($password)
    {
        $this->query->where('workstation.Passworthash', '=', $password);
        return $this;
    }
}
