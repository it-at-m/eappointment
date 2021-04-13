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
        $providerList = (new Provider())->readListBySource($session->getSource(), 0, null, $requestIdCsv);
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
            $providerList = (new Provider())->readListBySource($session->getSource(), 1, null, $requestIdCsv);
            $result = (count($providerList)) ? true : false;
        }
        return $result;
    }

    public static function testCurrentScopeHasRequest($process)
    {
        $testProcess = clone $process;
        $scope = (new \BO\Zmsdb\Scope)->readEntity($testProcess->getScopeId(), 2);
        $testProcess->scope = $scope;
        if (0 < count($testProcess->getRequestIds()) &&
            !$testProcess->getCurrentScope()->getRequestList()->hasRequests($testProcess->getRequestCSV())
        ) {
            throw new \BO\Zmsapi\Exception\Matching\RequestNotFound();
        }
    }
}
