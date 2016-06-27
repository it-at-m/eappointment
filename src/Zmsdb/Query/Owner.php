<?php

namespace BO\Zmsdb\Query;

class Owner extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'kunde';

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

    public function addConditionOwnerId($ownerId)
    {
        $this->query->where('owner.KundenID', '=', $ownerId);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Owner $entity)
    {
        $data = array();
        $data['Anschrift'] = $entity->contact['street'];
        $data['Kundenname'] = $entity->name;
        $data['TerminUrl'] = $entity->url;
        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }
}
