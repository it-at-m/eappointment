<?php

namespace BO\Zmsdb\Query;

class Mailtemplate extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailtemplate';

    const QUERY_SELECT = '
        SELECT * FROM mailtemplate
    ';

    const QUERY_SELECT_PROPERTY =
            'SELECT
                value
            FROM mailtemplate
            WHERE name = ?
            ';

    const QUERY_REPLACE_PROPERTY =
        'REPLACE INTO mailtemplate
            SET name  = :property, 
                value = :value
            ';


    protected $resolveLevel = 1;

    public function addConditionName($itemName)
    {
        $this->query->where(self::TABLE .'.name', '=', $itemName);
        return $this;
    }

    public function addTemplateContent($templateContent)
    {
        $this->query->values(array(
            'mailtemplate.value' => $templateContent
        ));        
        return $this;
    }

    public function getEntityMapping()
    {
        return [
            'name' => 'mailtemplate.name',
            'value' => 'mailtemplate.value'
        ];
    }


}
