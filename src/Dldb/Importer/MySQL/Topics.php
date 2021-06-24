<?php

namespace BO\Dldb\Importer\MySQL;

class Topics extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Topic';

    public function runImport() : bool {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() AS $topic) {
                    $topic = $this->createEntity($topic);
                    $topic->save();
                }
            }
            else {
                print_r('NO Topics(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
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