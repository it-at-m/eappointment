<?php

namespace BO\Zmsdb\Query;

class Organisation extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'organisation';

    public function getEntityMapping()
    {
        return [
            'contact__city' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", -1))'
            ),
            'contact__street' => self::expression('SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", 1)'),
            'contact__streetNumber' => self::expression(
                'TRIM("," FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`organisation`.`Anschrift`, ",", 1), " ", -1))'
            ),
            'contact__postalCode' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", -2), " ", 1))'
            ),
            'contact__region' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", -1))'
            ),
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'organisation.Organisationsname',
            'name' => 'organisation.Organisationsname',
            'id' => 'organisation.OrganisationsID',
            'preferences__ticketPrinterProtectionEnabled' => 'organisation.kioskpasswortschutz'
        ];
    }

    public function addConditionOrganisationId($organisationId)
    {
        $this->query->where('organisation.OrganisationsID', '=', $organisationId);
        return $this;
    }

    public function addConditionOwnerId($ownerId)
    {
        $this->query->where('organisation.KundenID', '=', $ownerId);
        return $this;
    }
}
