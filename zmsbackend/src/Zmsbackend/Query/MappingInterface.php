<?php

namespace BO\Zmsbackend\Query;

interface MappingInterface
{
    /**
     * @param mixed $type
     * @return array<string, mixed>
     */
    public function getEntityMapping($type = null);

    /**
     * @return array<string, mixed>
     */
    public function getReferenceMapping();
}
