<?php

namespace BO\Zmsdb\Query;

class Ticketprinter extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'kiosk';

    public function getEntityMapping()
    {
        return [
            'enabled' => 'ticketprinter.zugelassen',
            'hash' => 'ticketprinter.cookiecode',
            'id' => 'ticketprinter.kioskid',
            'lastUpdate' => 'ticketprinter.timestamp',
            'name' => 'ticketprinter.name',
        ];
    }

    public function addConditionTicketprinterId($ticketprinterId)
    {
        $this->query->where('ticketprinter.kioskid', '=', $ticketprinterId);
        return $this;
    }

    public function addConditionOrganisationId($organisationId)
    {
        $this->query->where('ticketprinter.organisationsid', '=', $organisationId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Ticketprinter $entity, $parentId = null)
    {
        $data = array();
        if (null !== $parentId) {
            $data['organisationsid'] = $parentId;
        }
        $data['zugelassen'] = ($entity->enabled) ? 1 : 0;
        $data['cookiecode'] = $entity->hash;
        $data['name'] = $entity->name;
        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
