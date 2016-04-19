<?php

namespace BO\Zmsdb\Query;

class Provider extends Base
{

    public static function getTablename()
    {
        $dbname_dldb = \BO\Zmsdb\Connection\Select::$dbname_dldb;
        return $dbname_dldb . '.dienstleister';
    }

    public function getEntityMapping()
    {
        return [
            'contact__email' => 'provider.email',
            'contact__city' => 'provider.adr_ort',
            'contact__country' => self::expression('"Germany"'),
            'contact__name' => 'provider.name',
            'contact__postalCode' => 'provider.adr_plz',
            'contact__region' => 'provider.adr_ort',
            'contact__street' => 'provider.adr_strasse',
            'contact__streetNumber' => 'provider.adr_hnr',
            'source' => self::expression('"dldb"'),
            'id' => 'provider.id',
            'link' => self::expression('CONCAT("https://service.berlin.de/standort/", `provider`.`id`, "/")'),
            'name' => 'provider.name',
        ];
    }

    public function addConditionProviderId($providerId)
    {
        $this->query->where('id', '=', $providerId);
        return $this;
    }

    public function addConditionProviderCsv($providerCsv)
    {
        $providerIds = \explode(',', $providerCsv);
        foreach ($providerIds as $providerId) {
            $this->query->orWhere('id', '=', $providerId);
        }
        return $this;
    }
}
