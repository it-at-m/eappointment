<?php

namespace BO\Zmsdb\Interfaces;

interface ResolveReferences
{
    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences);
}
