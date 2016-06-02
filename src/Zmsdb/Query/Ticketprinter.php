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
            'lastupdate' => 'ticketprinter.timestamp',
            'name' => 'ticketprinter.name',
            'organisation__id' => 'ticketprinter.organisationsid'
        ];
    }

    public function addJoin()
    {
        $this->query->leftJoin(
            new Alias(Organisation::TABLE, 'organisation'),
            'ticketprinter.organisationsid',
            '=',
            'organisation.OrganisationsID'
        );
        $organisationQuery = new Organisation($this->query);
        $organisationQuery->addEntityMappingPrefixed($this->getPrefixed('organisation__'));

        return [$organisationQuery];
    }

    public function addConditionOrganisationId($organisationId)
    {
        $this->query->where('ticketprinter.organisationsid', '=', $organisationId);
        return $this;
    }

    public function getReferenceMapping()
    {
        return [
            'organisation__$ref' => self::expression(
                'CONCAT("/organisation/", `ticketprinter`.`organisationsid`, "/")'
            ),
        ];
    }
}
