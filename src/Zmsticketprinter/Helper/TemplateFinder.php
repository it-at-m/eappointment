<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter\Helper;

class TemplateFinder
{
    const SUBPATH = '/page/customized';

    /**
     * get a customized Template if it exists, otherwise return default
     * department preferred before cluster
     *
     **/
    public static function getCustomizedSingleButtonTemplate($scope, $organisation)
    {
        $template = 'page/buttonSingleRow_default.twig';
        if ($scope->hasId() &&
            file_exists(self::getTemplatePath(). '/buttonSingleRow_scope_'. $scope->id .'.twig')
        ) {
            $template = self::SUBPATH .'/buttonSingleRow_scope_'. $scope->id .'.twig';
        }
        foreach ($organisation->departments as $department) {
            $department = new \BO\Zmsentities\Department($department);
            if ($department->hasId() &&
                file_exists(self::getTemplatePath(). '/buttonSingleRow_department_'. $department->id .'.twig')
            ) {
                $template = self::SUBPATH .'/buttonSingleRow_department_'. $department->id .'.twig';
            }
        }
        return $template;
    }

    public static function getCustomizedMultiButtonTemplate($buttons, $organisation)
    {
        $template = 'page/buttonMultiRow_default.twig';
        foreach (self::getClusterFromButtonList($buttons) as $cluster) {
            if (file_exists(self::getTemplatePath(). '/buttonMultiRow_cluster_'. $cluster['id'] .'.twig')) {
                $template = self::SUBPATH .'/buttonMultiRow_cluster_'. $cluster['id'] .'.twig';
            }
        }
        foreach ($organisation->departments as $department) {
            $department = new \BO\Zmsentities\Department($department);
            if ($department->hasId() &&
                file_exists(self::getTemplatePath(). '/buttonMultiRow_department_'. $department->id .'.twig')
            ) {
                $template = self::SUBPATH .'/buttonMultiRow_department_'. $department->id .'.twig';
            }
        }
        return $template;
    }

    protected static function getClusterFromButtonList($buttons)
    {
        $clusterList = array();
        foreach ($buttons as $button) {
            if ('cluster' == $button['type']) {
                $clusterList[] = $button['cluster'];
            }
        }
        return $clusterList;
    }

    /**
     * @todo check against ISO definition
     */
    protected static function getTemplatePath()
    {
        return realpath(__DIR__) .'/../../../templates'. self::SUBPATH;
    }
}
