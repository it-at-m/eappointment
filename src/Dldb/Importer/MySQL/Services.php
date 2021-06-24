<?php

namespace BO\Dldb\Importer\MySQL;

class Services extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Service';

    public function runImport() : bool {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() AS $service) {
                    $service = $this->createEntity($service);
                    $service->save();
                }
            }
            else {
                print_r('NO Services(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
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