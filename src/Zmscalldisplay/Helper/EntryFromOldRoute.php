<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay\Helper;

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
                    $scopes[] = $scope->getValue();
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
                    $clusters[] = $cluster->getValue();
                }
            }
        }
        return (0 < count($clusters)) ? implode(',', $clusters) : null;
    }

    public static function getFromOldRoute($request)
    {
        $collections['collections']['scopelist'] = self::getScopes($request);
        $collections['collections']['clusterlist'] = self::getClusters($request);
        return $collections;
    }
}
