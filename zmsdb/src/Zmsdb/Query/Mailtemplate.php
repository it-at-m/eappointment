<?php

namespace BO\Zmsdb\Query;

class Mailtemplate extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mail_template';

    const QUERY_SELECT = '
        SELECT * FROM mail_template
    ';

    const QUERY_SELECT_PROPERTY =
            'SELECT
                value
            FROM mail_template
            WHERE name = ?
            ';

    const QUERY_REPLACE_PROPERTY =
        'REPLACE INTO mail_template
            SET name  = :property, 
                value = :value
            ';


    protected $resolveLevel = 1;

    public function addConditionName($itemName)
    {
        $this->query->where('mailtemplate.name', '=', $itemName);
        return $this;
    }

    public function addConditionWithoutProvider()
    {
        $this->query->where('mailtemplate.provider', '=', '')->orWhere('mailtemplate.provider', 'IS', null);
        return $this;
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where('mailtemplate.provider', '=', $providerId);
        return $this;
    }

    public function addConditionId($templateId)
    {
        $this->query->where('mailtemplate.id', '=', $templateId);
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
            'id' => 'mailtemplate.id',
            'name' => 'mailtemplate.name',
            'value' => 'mailtemplate.value',
            'provider' => 'mailtemplate.provider'
        ];
    }
}
