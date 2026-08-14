<?php

namespace BO\Zmsdldb\Importer\MySQL;

class Topics extends Base
{
    /** @var class-string<\BO\Zmsdldb\Importer\MySQL\Entity\Base>|null */
    protected ?string $entityClass = \BO\Zmsdldb\Importer\MySQL\Entity\Topic::class;

    /** @psalm-api */
    #[\Override]
    public function runImport(): bool
    {
        try {
            if ($this->needsUpdate()) {
                foreach ($this->getIterator() as $topic) {
                    if (!is_array($topic)) {
                        continue;
                    }
                    $topic = $this->createEntity($topic);
                    $this->removeEntityFromCurrentList((int) $topic->get('id'));
                    $topic->save();
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
