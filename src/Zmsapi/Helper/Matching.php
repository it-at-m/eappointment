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
    public static function hasProviderRequest($session)
    {
        $requestIdCsv = $session->getRequests();
        $providerIdCsv = $session->getProviders();
        $providerList = (new Provider())->readListByRequest('dldb', $requestIdCsv);
        return $providerList->hasProvider($providerIdCsv);
    }

    public static function isProviderExisting($session)
    {
        $providerIdCsv = $session->getProviders();
        $providerList = (new Provider())->readList('dldb');
        return $providerList->hasProvider($providerIdCsv);
    }

    public static function isRequestExisting($session)
    {
        $requestIdCsv = $session->getRequests();
        $providerList = (new Provider())->readListByRequest('dldb', $requestIdCsv, 1);
        return (count($providerList)) ? true : false;
    }
}
