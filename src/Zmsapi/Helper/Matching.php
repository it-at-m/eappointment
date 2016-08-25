<?php

namespace BO\Zmsapi\Helper;

use \BO\Mellon\Validator;
use \BO\Zmsdb\Provider;
use \BO\Zmsdb\Request;

/**
 * example class to generate a response
 */
class Matching
{
    public static function hasProviderRequest($source, $requests, $providers)
    {
        $requestList = new \BO\Zmsentities\Collection\RequestList($requests);
        $requestIdCsv = $requestList->getIdsCsv();

        $providerList = new \BO\Zmsentities\Collection\ProviderList($providers);
        $providerIdCsv = $providerList->getIdsCsv();

        $providerList = (new Provider())->readListByRequest($source, $requestIdCsv);
        return $providerList->hasProvider($providerIdCsv);
    }

    public static function isProviderExisting($source, $providers)
    {
        $providerList = new \BO\Zmsentities\Collection\ProviderList($providers);
        $providerIdCsv = $providerList->getIdsCsv();
        $providerList = (new Provider())->readList($source);
        return $providerList->hasProvider($providerIdCsv);
    }

    public static function isRequestExisting($source, $requests)
    {
        $requestList = new \BO\Zmsentities\Collection\RequestList($requests);
        $requestIdCsv = $requestList->getIdsCsv();
        $providerList = (new Provider())->readListByRequest($source, $requestIdCsv, 1);
        return (count($providerList)) ? true : false;
    }
}
