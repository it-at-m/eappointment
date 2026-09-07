<?php

namespace BO\Zmsclient\GraphQL;

class GraphQLElement
{
    protected string $propertyName;

    public function __construct(string $propertyName = '__root')
    {
        $this->propertyName = $propertyName;
    }

    public function getPropertyName(): string
    {
        return $this->propertyName;
    }
}
