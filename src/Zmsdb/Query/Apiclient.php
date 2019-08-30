<?php

namespace BO\Zmsdb\Query;

/**
 * @SuppressWarnings(Public)
 */
class Apiclient extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'apiclient';

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

    public function postProcess($data)
    {
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        return $data;
    }
}
