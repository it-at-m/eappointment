<?php

namespace BO\Dldb\Importer\MySQL;

class Authorities extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Authority';

    public function runImport() : bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $authority) {
                    $authority = $this->createEntity($authority);
                    $this->removeEntityFromCurrentList($authority->get('id'));
                    $authority->save();
                }
            } else {
                print_r('NO Authorities(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
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
                ],1
            ));
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
