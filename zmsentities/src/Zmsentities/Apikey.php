<?php

namespace BO\Zmsentities;

class Apikey extends Schema\Entity
{
    public const PRIMARY = 'key';

    public static ?string $schema = "apikey.json";

    /**
     * @return Apiclient[]
     *
     * @psalm-return array{apiclient: Apiclient}
     */
    public function getDefaults()
    {
        return [
            'apiclient' => new Apiclient(),
        ];
    }

    public function setApiClient(Apiclient $apiClient): void
    {
        $this['apiclient'] = $apiClient;
    }

    public function getApiClient(): Apiclient
    {
        return $this['apiclient'];
    }

    /**
     * @return false|int
     *
     * @psalm-return false|int<0, max>
     */
    public function getQuotaPositionByRoute($route): int|false
    {
        return (isset($this->quota) && is_array($this->quota)) ?
            array_search($route, array_column($this->quota, 'route'))
            : false;
    }

    public function addQuota($route, $period): static
    {
        $this->quota[] = [
            'route' => $route,
            'period' => $period,
            'requests' => 1
        ];
        return $this;
    }

    public function updateQuota($position): static
    {
        $this->quota[$position]['requests']++;
        return $this;
    }
}
