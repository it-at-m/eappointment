<?php

namespace BO\Zmsbackend\Mail\Repository;

class Mailtemplate extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'mailtemplate';

    const string QUERY_SELECT = '
        SELECT * FROM mailtemplate
    ';

    const string QUERY_SELECT_PROPERTY =
            'SELECT
                value
            FROM mailtemplate
            WHERE name = ?
            ';

    const string QUERY_REPLACE_PROPERTY =
        'REPLACE INTO mailtemplate
            SET name  = :property, 
                value = :value
            ';


    protected $resolveLevel = 1;

    public function addConditionName($itemName): static
    {
        $this->query->where(self::TABLE . '.name', '=', $itemName);
        return $this;
    }

    public function addConditionWithoutProvider(): static
    {
        $this->query->where(self::TABLE . '.provider', '=', '')->orWhere(self::TABLE . '.provider', 'IS', null);
        return $this;
    }

    public function addConditionProviderId($providerId): static
    {
        $this->query->where(self::TABLE . '.provider', '=', $providerId);
        return $this;
    }

    public function addConditionId($templateId): static
    {
        $this->query->where(self::TABLE . '.id', '=', $templateId);
        return $this;
    }

    public function addTemplateContent($templateContent): static
    {
        $this->query->values(array(
            'mailtemplate.value' => $templateContent
        ));
        return $this;
    }

    /**
     * @psalm-api
     *
     * @return string[]
     *
     */
    public function getEntityMapping(): array
    {
        return [
            'id' => 'mailtemplate.id',
            'name' => 'mailtemplate.name',
            'value' => 'mailtemplate.value',
            'provider' => 'mailtemplate.provider'
        ];
    }
}
