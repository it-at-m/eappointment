<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmscalldisplay\Helper;

use BO\Mellon\Validator;
use BO\Slim\Request;
use Psr\Http\Message\RequestInterface;

class EntryFromOldRoute
{
    protected static $allowedStatus = array(
        'nurabholer' => 'pending'
    );

    /**
     * @param Request $request
     */
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

    /**
     * @param Request $request
     * @return string|null
     */
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

    /**
     * @param Request $request
     * @return string|null
     */
    protected static function getStatus($request)
    {
        $validator = $request->getAttribute('validator');
        $status = $validator->getParameter('nurabholer')
            ->isNumber()
            ->getValue();
        return ($status) ? self::$allowedStatus['nurabholer'] : null;
    }

    /**
     * @param Request|RequestInterface $request
     * @return array
     */
    public static function getFromOldRoute($request)
    {
        $collections['collections']['scopelist'] = self::getScopes($request);
        $collections['collections']['clusterlist'] = self::getClusters($request);
        $collections['queue']['status'] = self::getStatus($request);
        return $collections;
    }
}
