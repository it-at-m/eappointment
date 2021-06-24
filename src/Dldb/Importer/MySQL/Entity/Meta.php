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

    public function deleteEntity(): bool
    {
        try {
            return $this->deleteWith(
                array_combine(
                    ['object_id', 'locale', 'type'], 
                    array_values($this->get(['object_id', 'locale', 'type']))
                )
            );
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []) : bool {
        try {
            return $this->deleteWith(
                array_combine(['type', 'locale'], array_values($this->get(['type', 'locale'])))
            );
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function itemNeedsUpdate_(
        int $objectId = 0, 
        string $locale = '', 
        string $objectHash = '', 
        string $type = ''
    ) : bool {
        try {
            $statment = $this->getPDOAccess()->prepare(
                "SELECT count(1) AS count FROM meta WHERE object_id = ? AND locale = ? AND hash = ? AND type = ?"
            );
            $fields = $this->get(['object_id', 'locale', 'hash', 'type']);
            
            $r = $statment->execute(array_values($fields));

            $needsUpdate = false;
            if ($r) {
                $c = $statment->fetchColumn();
                if ($c != 1) {
                    $needsUpdate = true;
                }
            }
            #print_r([$needsUpdate ? 'T' : 'F', $fields]);
            return $needsUpdate;
        }
        catch (\Exception $e) {
            throw $e;
        }
        return false;
    }
}