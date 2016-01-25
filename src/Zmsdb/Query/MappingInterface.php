<?php

namespace BO\Zmsdb\Query;

interface MappingInterface
{
    public function getEntityMapping();
    public function getReferenceMapping();
}
