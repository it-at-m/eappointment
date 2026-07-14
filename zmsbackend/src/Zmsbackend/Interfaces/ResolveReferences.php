<?php

namespace BO\Zmsbackend\Interfaces;

interface ResolveReferences
{
    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences);
}
