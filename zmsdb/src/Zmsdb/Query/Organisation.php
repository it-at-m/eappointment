<?php

namespace BO\Zmsdb\Query;

class Organisation extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'organisation';

    /**
     * @return (Builder\Expression|string)[]
     *
     * @psalm-return array{contact__city: Builder\Expression, contact__street: 'organisation.Anschrift', contact__country: Builder\Expression, contact__name: 'organisation.Organisationsname', name: 'organisation.Organisationsname', id: 'organisation.OrganisationsID', preferences__ticketPrinterActivation: 'organisation.kiosk_activation'}
     */
    public function getEntityMapping()
    {
        return [
            'contact__city' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", -1))'
            ),
            'contact__street' => 'organisation.Anschrift',
            /*
            'contact__streetNumber' => self::expression(
                'TRIM("," FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`organisation`.`Anschrift`, ",", 1), " ", -1))'
            ),
            'contact__postalCode' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", -2), " ", 1))'
            ),
            'contact__region' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`organisation`.`Anschrift`, " ", -1))'
            ),
            */
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'organisation.Organisationsname',
            'name' => 'organisation.Organisationsname',
            'id' => 'organisation.OrganisationsID',
            'preferences__ticketPrinterActivation' => 'organisation.kiosk_activation'
        ];
    }

    public function addConditionOrganisationId($organisationId): static
    {
        $this->query->where('organisation.OrganisationsID', '=', $organisationId);
        return $this;
    }

    public function addConditionOwnerId($ownerId): static
    {
        $this->query->where('organisation.KundenID', '=', $ownerId);
        return $this;
    }

    public function addConditionScopeId($scopeId): static
    {
        $this->leftJoin(
            new Alias('behoerde', 'department'),
            'department.OrganisationsID',
            '=',
            'organisation.OrganisationsID'
        );
        $this->leftJoin(
            new Alias('standort', 'scope'),
            'scope.BehoerdenID',
            '=',
            'department.BehoerdenID'
        );
        $this->query->where('scope.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionDepartmentId($departmentId): static
    {
        $this->leftJoin(
            new Alias('behoerde', 'department'),
            'department.OrganisationsID',
            '=',
            'organisation.OrganisationsID'
        );
        $this->query->where('department.BehoerdenID', '=', $departmentId);
        return $this;
    }

    /**
     * @return (int|mixed|string)[]
     *
     * @psalm-return array<string, ''|0|1|14|mixed>
     */
    public function reverseEntityMapping(\BO\Zmsentities\Organisation $entity, $parentId = null): array
    {
        $data = array();
        if (null !== $parentId) {
            $data['KundenID'] = $parentId;
        }
        $data['Organisationsname'] = $entity->name;
        $data['InfoBezirkID'] = 14;
        $data['Anschrift'] = (isset($entity->contact['street'])) ? $entity->contact['street'] : '';
        $data['kiosk_activation'] = ($entity->getPreference('ticketPrinterActivation')) ? 1 : 0;
        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
        return $data;
    }
}
