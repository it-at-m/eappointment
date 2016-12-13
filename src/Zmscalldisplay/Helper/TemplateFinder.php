<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay\Helper;

class TemplateFinder
{
    const DEFAULT_TEMPLATE = '/page/default.twig';

    const SUBPATH = '/page/customized';

    /**
     * get a customized Template if it exists, otherwise return default
     * department preferred before cluster
     *
     **/
    public static function getCustomizedTemplate($calldisplay)
    {
        $template = null;
        //look for customized templates by single scope or single cluster
        if ($calldisplay->getScopeList()->getFirst()) {
            $entity = new \BO\Zmsentities\Scope($calldisplay->getScopeList()->getFirst());
            $template = self::getExistingTemplate($entity);
        }
        //look for customized template in clusterlist, overwrite template before
        foreach ($calldisplay->getClusterList() as $entity) {
            $entity = new \BO\Zmsentities\Cluster($entity);
            if (self::getExistingTemplate($entity)) {
                $template = self::getExistingTemplate($entity);
                break;
            }
        }
        //look for customized template in departmentlist, overwrite template before
        foreach ($calldisplay->organisation['departments'] as $departmentData) {
            $entity = new \BO\Zmsentities\Department($departmentData);
            if (self::getExistingTemplate($entity)) {
                $template = self::getExistingTemplate($entity);
                break;
            }
        }
        //look for customized template by organisation, overwrite template before
        $organisation = new \BO\Zmsentities\Organisation($calldisplay->organisation);
        if (self::getExistingTemplate($organisation)) {
            $template = self::getExistingTemplate($organisation);
        }
        return ($template) ? $template : self::DEFAULT_TEMPLATE;
    }

    public static function getButtonTemplateType($ticketprinter)
    {
        if (1 == count($ticketprinter->buttons)) {
            $buttonDisplay = 'button_single';
        } elseif (2 == count($ticketprinter->buttons)) {
            $buttonDisplay = 'button_multi_deep';
        } else {
            $buttonDisplay = 'button_multi';
        }
        return $buttonDisplay;
    }

    protected static function getExistingTemplate(\BO\Zmsentities\Schema\Entity $entity)
    {
        if ($entity->hasId() &&
            file_exists(
                self::getTemplatePath(). '/buttonDisplay_'. $entity->getEntityName() .'_'. $entity->id .'.twig'
            )
        ) {
            return self::SUBPATH .'/buttonDisplay_'. $entity->getEntityName() .'_'. $entity->id .'.twig';
        }
        return null;
    }

    /**
     * @todo check against ISO definition
     */
    protected static function getTemplatePath()
    {
        return realpath(__DIR__) .'/../../../templates'. self::SUBPATH;
    }
}
