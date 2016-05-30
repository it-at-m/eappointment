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
            'contact__street' => self::expression('SUBSTRING_INDEX(`owner`.`Anschrift`, " ", 1)'),
            'contact__streetNumber' => self::expression(
                'TRIM("," FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`owner`.`Anschrift`, ",", 1), " ", -1))'
            ),
            'contact__postalCode' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(SUBSTRING_INDEX(`owner`.`Anschrift`, " ", -2), " ", 1))'
            ),
            'contact__region' => self::expression(
                'TRIM(" " FROM SUBSTRING_INDEX(`owner`.`Anschrift`, " ", -1))'
            ),
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'owner.Kundenname',
            'id' => 'owner.KundenID',
            'name' => 'owner.Kundenname'
        ];
    }

    public function addConditionOwnerId($ownerId)
    {
        $this->query->where('owner.KundenID', '=', $ownerId);
        return $this;
    }
}
