<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Authorities extends Base
{
    protected $entityClass = '\\BO\\Zmsdldb\\Importer\\MySQL\\Entity\\Authority';

    #[\Override]
    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $authority) {
                    $authority = $this->createEntity($authority);
                    $this->removeEntityFromCurrentList($authority->get('id'));
                    $authority->save();
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
