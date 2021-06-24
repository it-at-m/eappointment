<?php

namespace BO\Dldb\Importer\MySQL;

class Locations extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Location';

    public function runImport() : bool {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() AS $location) {
                    $location = $this->createEntity($location);
                    $location->save();
                }
            }
            else {
                print_r('NO Locations(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
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