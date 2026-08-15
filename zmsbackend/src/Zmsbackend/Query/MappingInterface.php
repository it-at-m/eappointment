<?php

namespace BO\Zmsbackend\Query;

interface MappingInterface
{
    /** @psalm-api */
    public function getEntityMapping();
    /** @psalm-api */
    public function getReferenceMapping();
}
