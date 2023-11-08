<?php

namespace BO\Zmsdb\Query;

class Cluster extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'standortcluster';

    public function getQueryWriteAssignedScopes()
    {
        return '
            REPLACE INTO `clusterzuordnung`
            SET
                clusterID=:clusterId,
                standortID=:scopeId
        ';
    }

    public function getQueryDeleteAssignedScopes()
    {
        return '
            DELETE FROM `clusterzuordnung`
            WHERE
                `clusterID` = :clusterId
        ';
    }

    public function getEntityMapping()
    {
        return [
            'id' => 'cluster.clusterID',
            'name' => 'cluster.name',
            'hint' => 'cluster.clusterinfozeile1',
            'shortNameEnabled' => 'cluster.standortkuerzelanzeigen',
            'callDisplayText' => 'cluster.aufrufanzeigetext'
        ];
    }

    public function addConditionClusterId($clusterId)
    {
        $this->query->where('cluster.clusterID', '=', $clusterId);
        return $this;
    }

    public function addConditionDepartmentId($departementId)
    {
        $this->leftJoin(
            new Alias('standort', 'scope'),
            'scope.StandortID',
            '=',
            'cluster_scope.standortID'
        );
        $this->query->where('scope.BehoerdenID', '=', $departementId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->leftJoin(
            new Alias('standort', 'scope'),
            'scope.StandortID',
            '=',
            'cluster_scope.standortID'
        );
        $this->query->where('scope.StandortID', '=', $scopeId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Cluster $entity)
    {
        $data = array();
        $data['name'] = $entity->name;
        $data['clusterinfozeile1'] = $entity->hint;
        $data['standortkuerzelanzeigen'] = ($entity->shortNameEnabled)  ? 1 : 0;
        $data['aufrufanzeigetext'] = $entity->callDisplayText;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }

    public function addRequiredJoins()
    {
        $this->leftJoin(
            new Alias('clusterzuordnung', 'cluster_scope'),
            'cluster.clusterID',
            '=',
            'cluster_scope.clusterID'
        );
    }
}
