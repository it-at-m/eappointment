<?php

namespace BO\Zmsentities\Schema;

use BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(NumberOfChildren)
 */
class Factory
{
    /**
     * @var Array $data unserialized entity
     */
    protected $data = null;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public static function create($data)
    {
        return new self($data);
    }

    /**
     * Detect type of entity and return initialized object
     *
     * @return Entity
     */
    public function getEntity()
    {
        $entityName = $this->getEntityName();
        $class = "\\BO\\Zmsentities\\$entityName";
        return new $class(new UnflattedArray($this->data));
    }

    /**
     * Parse schema and return Entity name
     *
     * @return String
     */
    public function getEntityName()
    {
        if (!Property::__keyExists('$schema', $this->data)) {
            throw new \BO\Zmsentities\Exception\SchemaMissingKey('Missing $schema-key on given data.');
        }
        $schema = $this->data['$schema'];
        $entityName = preg_replace('#^.*/([^/]+)\.json#', '$1', $schema);
        return ucfirst($entityName);
    }
}
