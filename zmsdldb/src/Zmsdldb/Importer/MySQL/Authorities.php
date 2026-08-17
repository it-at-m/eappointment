<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Authorities extends Base
{
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\MySQL\Entity\Authority::class;

    /** @psalm-api */
    #[\Override]
    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $authority) {
                    if (!is_array($authority)) {
                        continue;
                    }
                    $authority = $this->createEntity($authority);
                    $this->removeEntityFromCurrentList((int) $authority->get('id'));
                    $authority->save();
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
