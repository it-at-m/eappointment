<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Locations extends Base
{
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\MySQL\Entity\Location::class;

    /** @psalm-api */
    #[\Override]
    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $location) {
                    if (!is_array($location)) {
                        continue;
                    }
                    $location = $this->createEntity($location);
                    $this->removeEntityFromCurrentList((int) $location->get('id'));
                    $location->save();
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
