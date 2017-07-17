<?php

namespace BO\Zmsentities\Useraccount;

class EntityAccess implements RightsInterface
{
    protected $entity;

    public function __construct(AccessInterface $entity)
    {
        $this->entity = $entity;
    }

    public function validateUseraccount(\BO\Zmsentities\Useraccount $useraccount)
    {
        return $this->entity->hasAccess($useraccount);
    }
}
