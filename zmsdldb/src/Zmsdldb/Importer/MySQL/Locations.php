<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Locations extends Base
{
    protected $entityClass = '\\BO\\Zmsdldb\\Importer\\MySQL\\Entity\\Location';

    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $location) {
                    $location = $this->createEntity($location);
                    $this->removeEntityFromCurrentList($location->get('id'));
                    $location->save();
                }
            }
            foreach ($this->getCurrentEntitys() as $entityToDelete) {
                $entityToDelete->delete();
            }

            $this->saveMetaObject();
        } catch (\Exception $e) {
            throw $e;
        }
        return true;
    }
}
