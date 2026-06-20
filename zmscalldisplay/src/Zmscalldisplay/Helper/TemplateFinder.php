<?php

/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmscalldisplay\Helper;

use BO\Zmscalldisplay\Exception\TemplateNotFound;
use BO\Zmsentities\Schema\Entity;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Cluster;
use BO\Zmsentities\Department;

class TemplateFinder
{
    protected $defaultTemplate;
    protected $subPath;

    protected $template;

    public function __construct($defaultTemplate = "defaultplatz", $subPath = '/page/customized')
    {
        $this->subPath = $subPath;
        $this->template = $subPath . '/' . $defaultTemplate . '.twig';
        if ($this->isTemplateReadable($this->template)) {
            $this->defaultTemplate = $defaultTemplate;
        } else {
            throw new TemplateNotFound("Could not find template $this->template");
        }
    }

    public function getTemplate()
    {
        return $this->template;
    }

    public function setCustomizedTemplate($calldisplay)
    {
        $template = null;
        if ($this->defaultTemplate == 'defaultplatz') {
            $template = $this->getTemplateBySettings($calldisplay);
        }
        $this->template = ($template) ? $template : $this->template;
        return $this;
    }

    protected function getTemplateBySettings($calldisplay)
    {
        $template = null;
        //look for customized templates by single scope or single cluster
        if ($calldisplay->getScopeList()->getFirst()) {
            $entity = new Scope($calldisplay->getScopeList()->getFirst());
            $template = $this->getExistingTemplate($entity);
        }
        //look for customized template in clusterlist, overwrite template before
        foreach ($calldisplay->getClusterList() as $entity) {
            $entity = new Cluster($entity);
            if ($this->getExistingTemplate($entity)) {
                $template = $this->getExistingTemplate($entity);
                break;
            }
        }
        //look for customized template in departmentlist, overwrite template before
        foreach ($calldisplay->organisation['departments'] as $departmentData) {
            $entity = new Department($departmentData);
            if ($this->getExistingTemplate($entity)) {
                $template = $this->getExistingTemplate($entity);
                break;
            }
        }

        return $template;
    }

    protected function getExistingTemplate(Entity $entity)
    {
        $path = $this->subPath . '/calldisplay_' . $entity->getEntityName() . '_' . $entity->getId() . '.twig';
        if ($entity->hasId() && $this->isTemplateReadable($path)) {
            return $path;
        }

        return null;
    }

    protected function isTemplateReadable($path)
    {
        return is_readable($this->getTemplatePath() . $path);
    }

    public function getTemplatePath()
    {
        return realpath(__DIR__) . '/../../../templates';
    }
}
