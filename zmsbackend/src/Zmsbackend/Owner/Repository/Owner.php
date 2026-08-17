<?php

namespace BO\Zmsbackend\Owner\Repository;

class Owner extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'kunde';

    /**
     * @return (\BO\Zmsbackend\Query\Builder\Expression|string)[]
     *
     */
    #[\Override]
    public function getEntityMapping()
    {
        return [
            'contact__city' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`owner`.`Anschrift`, " ", -1))'
            ),
            'contact__street' => 'owner.Anschrift',
            /*
            'contact__streetNumber' => self::expression(
                'TRIM("," FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`owner`.`Anschrift`, ",", 1), " ", -1))'
            ),
            'contact__postalCode' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`owner`.`Anschrift`, " ", -2), " ", 1))'
            ),
            'contact__region' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`owner`.`Anschrift`, " ", -1))'
            ),
            */
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'owner.Kundenname',
            'id' => 'owner.KundenID',
            'name' => 'owner.Kundenname',
            'url' => 'owner.TerminURL'
        ];
    }

    public function addConditionOrganisationId($organisationId): static
    {
        $this->leftJoin(
            new \BO\Zmsbackend\Query\Alias(\BO\Zmsbackend\Organisation\Repository\Organisation::TABLE, 'ownerorganisation'),
            'owner.KundenID',
            '=',
            'ownerorganisation.KundenID'
        );
        $this->query->where('ownerorganisation.OrganisationsID', '=', $organisationId);
        return $this;
    }

    public function addConditionOwnerId(int|string $ownerId): static
    {
        $this->query->where('owner.KundenID', '=', $ownerId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Owner $entity): array
    {
        $data = array();
        $data['Anschrift'] = $entity->contact['street'] ?? null;
        $data['Kundenname'] = $entity->name;
        $data['TerminUrl'] = $entity['url'] ?? null;
        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
