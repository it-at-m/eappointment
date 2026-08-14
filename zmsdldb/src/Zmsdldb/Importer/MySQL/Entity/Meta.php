<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

class Meta extends Base
{
    protected array $fieldMapping = [
        'object_id' => 'object_id',
        'hash' => 'hash',
        'locale' => 'locale',
        'lastupdate' => 'lastupdate',
        'keywords' => 'keywords',
        'url' => 'url',
        'type' => 'type',
        'titles' => 'titles_json'
    ];

    #[\Override]
    protected function setupMapping(): void
    {
        $this->referanceMapping = [
            /*
            'keywords' => [
                'class' => Search::class,
                'neededFields' => [
                    'object_id' => 'object_id',
                    'locale' => 'locale',
                    'keywords' => 'search_value',
                    'type' => 'entity_type',
                ],
                'addFields' => [
                    'search_type' => 'keywords'
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('locale'),
                    'entity_type' => $this->get('type')
                ],
                'multiple' => false,
                'clearFields' => [
                    'entity_type' => $this->get('type'),
                    'locale' => $this->get('locale')
                ],
                'selfAsArray' => true
            ],
            'titles' => [
                'class' => Search::class,
                'neededFields' => [
                    'object_id' => 'object_id',
                    'locale' => 'locale',
                    'titles' => 'search_value',
                    'type' => 'entity_type',
                ],
                'addFields' => [
                    'search_type' => 'titles'
                ],
                'deleteFields' => [
                    'object_id' => $this->get('id'),
                    'locale' => $this->get('locale'),
                    'entity_type' => $this->get('type')
                ],
                'multiple' => false,
                'clearFields' => [
                    'entity_type' => $this->get('type'),
                    'locale' => $this->get('locale')
                ],
                'selfAsArray' => true
            ]*/
        ];
    }

    #[\Override]
    public function postSetupFields(): void
    {
        $lastupdate = $this->fields['lastupdate'] ?? null;
        $created = is_string($lastupdate) && $lastupdate !== '' ? date_create($lastupdate) : false;
        if ($created instanceof \DateTimeInterface) {
            $this->fields['lastupdate'] = $created->format('Y-m-d H:i:s');
        } else {
            $this->fields['lastupdate'] = '1970-01-01 01:00:00';
        }
    }

    #[\Override]
    public function deleteEntity(): void
    {
        try {
            $this->deleteWith(
                array_combine(
                    ['object_id', 'locale', 'type'],
                    array_values($this->get(['object_id', 'locale', 'type']))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    #[\Override]
    public function clearEntity(array $addWhere = []): void
    {
        try {
            $this->deleteWith(
                array_combine(['type', 'locale'], array_values($this->get(['type', 'locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function itemNeedsUpdateAlt(): bool
    {
        try {
            $statment = $this->getPDOAccess()->prepare(
                "SELECT count(1) AS count FROM meta WHERE object_id = ? AND locale = ? AND hash = ? AND type = ?"
            );
            $fields = $this->get(['object_id', 'locale', 'hash', 'type']);

            $result = $statment->execute(array_values($fields));

            $needsUpdate = false;
            if ($result) {
                $count = $statment->fetchColumn();
                if ($count != 1) {
                    $needsUpdate = true;
                }
            }
            return $needsUpdate;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
