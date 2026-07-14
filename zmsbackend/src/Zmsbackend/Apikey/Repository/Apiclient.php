<?php

namespace BO\Zmsbackend\Apikey\Repository;

/**
 * @SuppressWarnings(Public)
 */
class Apiclient extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'apiclient';

    #[\Override]
    public function getEntityMapping()
    {
        $mapping = [
            'apiClientID' => 'apiclient.apiClientID',
            'clientKey' => 'apiclient.clientKey',
            'shortname' => 'apiclient.shortname',
            'accesslevel' => 'apiclient.accesslevel',
            'lastChange' => 'apiclient.updateTimestamp',
        ];
        return $mapping;
    }

    public function addConditionApiclientKey($clientKey)
    {
        $this->query->where('apiclient.clientKey', '=', $clientKey);
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
