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
     *
     * @var int
     */
    protected int $resolveLevel = 0;

    public function getOrganisationIdByHash(): string
    {
        return '
            SELECT organisationsid
            FROM `kiosk` ticketprinter
            WHERE ticketprinter.`cookiecode` = :hash';
    }

    /**
     * @return (Builder\Expression|string)[]
     *
     * @psalm-return array{enabled: Builder\Expression, hash: 'ticketprinter.cookiecode', id: 'ticketprinter.kioskid', lastUpdate: 'ticketprinter.timestamp', name: 'ticketprinter.name'}
     */
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

    public function addConditionTicketprinterId($ticketprinterId): static
    {
        $this->query->where('ticketprinter.kioskid', '=', $ticketprinterId);
        return $this;
    }

    public function addConditionOrganisationId($organisationId): static
    {
        $this->query->where('ticketprinter.organisationsid', '=', $organisationId);
        return $this;
    }

    public function addConditionHash($hash): static
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
     * @psalm-return array<string, int<0, max>|mixed>
     */
    public function reverseEntityMapping(\BO\Zmsentities\Ticketprinter $entity, $organisationId): array
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
