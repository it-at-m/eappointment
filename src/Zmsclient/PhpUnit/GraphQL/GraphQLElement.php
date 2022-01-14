<?php

namespace BO\Zmsclient\PhpUnit\GraphQL;

class GraphQLElement
{
    protected $propertyName;
    
    public function __construct($propertyName = '__root')
    {
        $this->propertyName = $propertyName;
    }
}
