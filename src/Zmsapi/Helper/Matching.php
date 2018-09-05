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
        $result = true;
        $requestIdCsv = $session->getRequests();
        $providerList = (new Provider())->readListByRequest($session->getSource(), $requestIdCsv);
        if ($session->hasProvider()) {
            $providerIdCsv = $session->getProviders();
            $result = $providerList->hasProvider($providerIdCsv);
        } elseif ($session->hasScope()) {
            $scope = (new \BO\Zmsdb\Scope())->readEntity($session->getScope(), 1);
            $result = $providerList->hasProvider($scope->getProviderId());
        }
        return $result;
    }

    public static function isProviderExisting($session)
    {
        $result = true;
        if ($session->hasProvider()) {
            $providerIdCsv = $session->getProviders();
            $providerList = (new Provider())->readListBySource($session->getSource());
            $result = $providerList->hasProvider($providerIdCsv);
        }
        return $result;
    }

    public static function isRequestExisting($session)
    {
        $result = true;
        if ($session->hasRequests()) {
            $requestIdCsv = $session->getRequests();
            $providerList = (new Provider())->readListByRequest($session->getSource(), $requestIdCsv, 1);
            $result = (count($providerList)) ? true : false;
        }
        return $result;
    }
}
