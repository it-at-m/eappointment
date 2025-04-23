<?php

namespace BO\Dldb\Importer\MySQL;

class Services extends Base
{
    protected $entityClass = '\\BO\\Dldb\\Importer\\MySQL\\Entity\\Service';

    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $service) {
                    $service = $this->createEntity($service);
                    $this->removeEntityFromCurrentList($service->get('id'));
                    $service->save();
                }
            } else {
                print_r('NO Services(' . $this->getLocale() . ') Update needet' . \PHP_EOL);
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
