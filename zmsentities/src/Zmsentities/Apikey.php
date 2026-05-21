<?php

namespace BO\Zmsentities;

class Apikey extends Schema\Entity
{
    public const PRIMARY = 'key';

    public static $schema = "apikey.json";

    public function getDefaults()
    {
        return [
            'apiclient' => new Apiclient(),
        ];
    }

    public function setApiClient(Apiclient $apiClient)
    {
        $this['apiclient'] = $apiClient;
    }

    public function getApiClient(): Apiclient
    {
        return $this['apiclient'];
    }

    public function getQuotaPositionByRoute($route)
    {
        return (isset($this->quota) && is_array($this->quota)) ?
            array_search($route, array_column($this->quota, 'route'))
            : false;
    }

    public function addQuota($route, $period)
    {
        $this->quota[] = [
            'route' => $route,
            'period' => $period,
            'requests' => 1
        ];
        return $this;
    }

    public function updateQuota($position)
    {
        $this->quota[$position]['requests']++;
        return $this;
    }
}
