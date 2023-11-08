<?php

namespace BO\Dldb\Importer\MySQL;

class Topics extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Topic';

    public function runImport() : bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $topic) {
                    $topic = $this->createEntity($topic);
                    $this->removeEntityFromCurrentList($topic->get('id'));
                    $topic->save();
                }
            } else {
                print_r('NO Topics(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
                #print_r($this->metaObject);
            }
            /*
            error_log(
                print_r([
                    'delete',
                    $this->entityClass::getTableName(),
                    $this->getLocale(),
                    count($this->getCurrentEntitys()),
                    array_keys($this->getCurrentEntitys())
                ],
            1));
            */
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
