<?php

namespace BO\Zmsdb\Query;

class Ticketprinter extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'kiosk';

    const PRIMARY = 'hash';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getOrganisationIdByHash()
    {
        return '
            SELECT organisationsid
            FROM `kiosk` ticketprinter
            WHERE ticketprinter.`cookiecode` = :hash';
    }

    public function getEntityMapping()
    {
        return [
            'enabled' => self::expression('CAST(`ticketprinter`.`zugelassen` AS SIGNED)'),
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

    public function addConditionDeleteInterval($expirationDate)
    {
        $this->query->where('ticketprinter.timestamp', '<=', $expirationDate->getTimestamp());
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Ticketprinter $entity, $organisationId)
    {
        $data = array();
        $data['organisationsid'] = $organisationId;
        $data['zugelassen'] = ($entity->isEnabled()) ? 1 : 0;
        $data['cookiecode'] = $entity->getId();
        $data['timestamp'] = time();
        $data['name'] = $entity->toProperty()->name->get();
        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }
}
