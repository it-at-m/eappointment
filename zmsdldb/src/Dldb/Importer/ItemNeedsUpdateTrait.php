<?php

namespace BO\Dldb\Importer;

/**
 *
 */
trait ItemNeedsUpdateTrait
{
    public function itemNeedsUpdate(int $objectId, string $locale, string $objectHash, string $type): bool
    {
        try {
            $statment = $this->getPDOAccess()->prepare(
                "SELECT count(1) AS count FROM meta WHERE object_id = ? AND locale = ? AND hash = ? AND type = ?"
            );
            $result = $statment->execute([$objectId, $locale, $objectHash, $type]);

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
