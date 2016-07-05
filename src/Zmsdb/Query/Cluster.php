<?php

namespace BO\Zmsdb\Query;

class Cluster extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'standortcluster';

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
        $this->query->leftJoin(
            new Alias('standort', 'scope'),
            'scope.StandortID',
            '=',
            'cluster_scope.standortID'
        );
        $this->query->where('scope.BehoerdenID', '=', $departementId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Owner $entity)
    {
        $data = array();
        $data['name'] = $entity->name;
        $data['clusterinfozeile1'] = $entity->hint;
        $data['standortkuerzelanzeigen'] = ($entity->shortNameEnabled)  ? 1 : 0;
        $data['aufrufanzeigetext'] = ($entity->callDisplayText) ? 1 : 0;

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }

    public function addRequiredJoins()
    {
        $this->query->leftJoin(
            new Alias('clusterzuordnung', 'cluster_scope'),
            'cluster.clusterID',
            '=',
            'cluster_scope.clusterID'
        );
    }
}
