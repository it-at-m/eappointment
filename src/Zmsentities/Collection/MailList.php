<?php
namespace BO\Zmsentities\Collection;

class MailList extends Base
{

    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }
}
