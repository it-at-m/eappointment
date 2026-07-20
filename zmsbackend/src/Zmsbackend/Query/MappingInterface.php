<?php

namespace BO\Zmsbackend\Query;

interface MappingInterface
{
    public function getEntityMapping();
    public function getReferenceMapping();
}
