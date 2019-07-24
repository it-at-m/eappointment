<?php

namespace BO\Zmsapi\Response;

class GraphQLElement
{
    protected $propertyName;
    
    public function __construct($propertyName = '__root')
    {
        $this->propertyName = $propertyName;
    }
}
