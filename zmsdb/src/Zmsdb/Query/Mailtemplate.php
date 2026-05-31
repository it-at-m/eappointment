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


    protected int $resolveLevel = 1;

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
     * @return string[]
     *
     * @psalm-return array{id: 'mailtemplate.id', name: 'mailtemplate.name', value: 'mailtemplate.value', provider: 'mailtemplate.provider'}
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
