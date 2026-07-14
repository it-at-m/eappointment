<?php

namespace BO\Zmsbackend\Source\Repository;

class Source extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'source';

    #[\Override]
    public function getEntityMapping()
    {
        return [
            'source' => 'source.source',
            'label' => 'source.label',
            'editable' => 'source.editable',
            'contact__name' => 'source.contact__name',
            'contact__email' => 'source.contact__email',
            'lastChange' => 'source.lastChange',
        ];
    }

    public function addConditionSource($source)
    {
        $this->query->where('source.source', '=', $source);
        return $this;
    }

    #[\Override]
    public function postProcess($data)
    {
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsbackend\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        return $data;
    }
}
