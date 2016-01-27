<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Request as Entity;

class Request extends Base
{
    public function readEntity($source, $requestId)
    {
        if ('dldb' !== $source) {
            return new Entity();
        }
        $query = new Query\Request(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionRequestId($requestId);
        return $this->fetchOne($query, new Entity());
    }

    public function readSlotsOnEntity(\BO\Zmsentities\Request $entity)
    {
        $query = 'SELECT
                x.`dienstleister` AS provider__id,
                x.`slots`
            FROM `startinfo`.`xdienst` x
                LEFT JOIN `startinfo`.`dienstleister` d ON x.dienstleister = d.id
            WHERE
                x.`dienstleistung` = :request_id
                AND x.`termin_hide` = 0
                AND d.`zms_termin` = 1
        ';
        $providerSlots = $this->getReader()->fetchAll($query, ['request_id' => $entity->id]);
        return $providerSlots;
    }
}
