<?php

namespace BO\Dldb\Importer\MySQL;

class Authorities extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Authority';

    public function runImport() : bool {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() AS $authority) {
                    $authority = $this->createEntity($authority);
                    $authority->save();
                }
            }
            else {
                print_r('NO Authorities(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
                #print_r($this->metaObject);
            }
            $this->saveMetaObject();
        }
        catch (\Exception $e) {
            throw $e;
        }
    
        return true;
    }
}