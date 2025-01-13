<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Meta extends Base
{
    protected $fieldMapping = [
        'object_id' => 'object_id',
        'hash' => 'hash',
        'locale' => 'locale',
        'lastupdate' => 'lastupdate',
        'keywords' => 'keywords',
        'url' => 'url',
        'type' => 'type',
        'titles' => 'titles_json'
    ];

    protected function setupMapping()
    {
        $this->referanceMapping = [
            /*
            'keywords' => [
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
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
                'class' => 'BO\\Dldb\\Importer\\MySQL\\Entity\\Search',
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

    public function postSetupFields()
    {
        if (array_key_exists('lastupdate', $this->fields) && !empty($this->fields['lastupdate'])) {
            $this->fields['lastupdate'] = date_format(date_create($this->fields['lastupdate']), 'Y-m-d H:i:s');
        } elseif (!array_key_exists('lastupdate', $this->fields) || empty($this->fields['lastupdate'])) {
            $this->fields['lastupdate'] = '1970-01-01 01:00:00';
        }
    }

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['object_id', 'locale', 'type'],
                    array_values($this->get(['object_id', 'locale', 'type']))
                )
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []): bool
    {
        try {
            return $this->deleteWith(
                array_combine(['type', 'locale'], array_values($this->get(['type', 'locale'])))
            );
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function itemNeedsUpdateAlt(
        int $objectId = 0,
        string $locale = '',
        string $objectHash = '',
        string $type = ''
    ): bool {
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
            #print_r([$needsUpdate ? 'T' : 'F', $fields]);
            return $needsUpdate;
        } catch (\Exception $e) {
            throw $e;
        }
        return false;
    }
}
