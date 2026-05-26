<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Topics extends Base
{
    protected $entityClass = '\\BO\\Zmsdldb\\Importer\\MySQL\\Entity\\Topic';

    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $topic) {
                    $topic = $this->createEntity($topic);
                    $this->removeEntityFromCurrentList($topic->get('id'));
                    $topic->save();
                }
            } else {
                $this->logNoUpdateNeeded();
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
