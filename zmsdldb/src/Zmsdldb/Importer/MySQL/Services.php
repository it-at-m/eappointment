<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Services extends Base
{
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\MySQL\Entity\Service::class;

    /** @psalm-api */
    #[\Override]
    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $service) {
                    if (!is_array($service)) {
                        continue;
                    }
                    $service = $this->createEntity($service);
                    $this->removeEntityFromCurrentList((int) $service->get('id'));
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
