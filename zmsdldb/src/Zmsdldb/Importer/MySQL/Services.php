<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Services extends Base
{
    protected $entityClass = '\\BO\\Zmsdldb\\Importer\\MySQL\\Entity\\Service';

    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $service) {
                    $service = $this->createEntity($service);
                    $this->removeEntityFromCurrentList($service->get('id'));
                    $service->save();
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
