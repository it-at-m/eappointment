<?php

namespace BO\Zmsbackend\Ticketprinter\Repository;

class Ticketprinter extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'kiosk';

    const string PRIMARY = 'hash';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getOrganisationIdByHash(): string
    {
        return '
            SELECT organisationsid
            FROM `kiosk` ticketprinter
            WHERE ticketprinter.`cookiecode` = :hash';
    }

    /**
     * @return (\BO\Zmsbackend\Query\Builder\Expression|string)[]
     *
     */
    #[\Override]
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

    public function addConditionTicketprinterId(int|string $ticketprinterId): static
    {
        $this->query->where('ticketprinter.kioskid', '=', $ticketprinterId);
        return $this;
    }

    public function addConditionOrganisationId(int $organisationId): static
    {
        $this->query->where('ticketprinter.organisationsid', '=', $organisationId);
        return $this;
    }

    public function addConditionHash(string $hash): static
    {
        $this->query->where('ticketprinter.cookiecode', '=', $hash);
        return $this;
    }

    public function addConditionDeleteInterval($expirationDate): static
    {
        $this->query->where('ticketprinter.timestamp', '<=', $expirationDate->getTimestamp());
        return $this;
    }

    /**
     * @return (int|mixed)[]
     *
     */
    public function reverseEntityMapping(\BO\Zmsentities\Ticketprinter $entity, int $organisationId): array
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
