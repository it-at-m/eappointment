<?php

namespace BO\Zmsdb\Query;

class Ticketprinter extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'kiosk';

    const PRIMARY = 'hash';

    public function getEntityMapping()
    {
        return [
            'enabled' => 'ticketprinter.zugelassen',
            'hash' => 'ticketprinter.cookiecode',
            'id' => 'ticketprinter.kioskid',
            'lastUpdate' => 'ticketprinter.timestamp',
            'name' => 'ticketprinter.name'
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

    public function addConditionHash($hash)
    {
        $this->query->where('ticketprinter.cookiecode', '=', $hash);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Ticketprinter $entity, $organisationId)
    {
        $data = array();
        $data['organisationsid'] = $organisationId;
        $data['zugelassen'] = ($entity->toProperty()->enabled->get()) ? 1 : 0;
        $data['cookiecode'] = $entity->hash;
        $data['name'] = $entity->toProperty()->name->get();
        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
