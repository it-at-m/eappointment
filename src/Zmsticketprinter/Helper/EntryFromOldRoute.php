<?php

/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

use BO\Mellon\Validator;

class EntryFromOldRoute
{
    protected static function getScopes($request)
    {
        $scopes = [ ];
        $validator = $request->getAttribute('validator');
        $scopeData = $validator->getParameter('auswahlstandortid')
            ->isArray()
            ->getValue();
        if ($scopeData) {
            foreach ($scopeData as $scope) {
                $scope = Validator::value($scope)->isNumber();
                if (! $scope->hasFailed()) {
                    $scopes[] = 's'. $scope->getValue();
                }
            }
        }
        return (0 < count($scopes)) ? implode(',', $scopes) : null;
    }

    protected static function getClusters($request)
    {
        $clusters = [ ];
        $validator = $request->getAttribute('validator');
        $clusterData = $validator->getParameter('auswahlclusterid')
            ->isArray()
            ->getValue();
        if ($clusterData) {
            foreach ($clusterData as $cluster) {
                $cluster = Validator::value($cluster)->isNumber();
                if (! $cluster->hasFailed()) {
                    $clusters[] = 'c'. $cluster->getValue();
                }
            }
        }
        return (0 < count($clusters)) ? implode(',', $clusters) : null;
    }

    public static function getFromOldMehrfachKiosk($request)
    {
        $buttonList = '';
        $scopes = self::getScopes($request);
        $clusters = self::getClusters($request);
        $buttonList = implode(',', array_filter(array($scopes, $clusters)));
        return $buttonList;
    }
}
