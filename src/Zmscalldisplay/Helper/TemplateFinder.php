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
    protected $defaultTemplate;
    protected $subPath;

    protected $template;

    public function __construct($defaultTemplate = "default", $subPath = '/page/customized')
    {
        $this->subPath = $subPath;
        $this->template = $subPath . '/' . $defaultTemplate . '.twig';
        if ($this->isTemplateReadable($this->template)) {
            $this->defaultTemplate = $defaultTemplate;
        } else {
            throw new \BO\Zmscalldisplay\Exception\TemplateNotFound("Could not find template $this->template");
        }
    }

    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * get a customized Template if it exists, otherwise return default
     * department preferred before cluster
     *
     **/
    public function setCustomizedTemplate($calldisplay)
    {
        $template = null;
        if ($this->defaultTemplate == 'default') {
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
            $entity = new \BO\Zmsentities\Scope($calldisplay->getScopeList()->getFirst());
            $template = $this->getExistingTemplate($entity);
        }
        //look for customized template in clusterlist, overwrite template before
        foreach ($calldisplay->getClusterList() as $entity) {
            $entity = new \BO\Zmsentities\Cluster($entity);
            if ($this->getExistingTemplate($entity)) {
                $template = $this->getExistingTemplate($entity);
                break;
            }
        }
        //look for customized template in departmentlist, overwrite template before
        foreach ($calldisplay->organisation['departments'] as $departmentData) {
            $entity = new \BO\Zmsentities\Department($departmentData);
            if ($this->getExistingTemplate($entity)) {
                $template = $this->getExistingTemplate($entity);
                break;
            }
        }
        return $template;
    }

    protected function getExistingTemplate(\BO\Zmsentities\Schema\Entity $entity)
    {
        $path = $this->subPath .'/calldisplay_'. $entity->getEntityName() .'_'. $entity->id .'.twig';
        if ($entity->hasId() && $this->isTemplateReadable($path)) {
            return $path;
        }
        return null;
    }

    protected function isTemplateReadable($path)
    {
        return is_readable($this->getTemplatePath() . $path);
    }

    /**
     * @todo check against ISO definition
     */
    protected function getTemplatePath()
    {
        return realpath(__DIR__) .'/../../../templates';
    }
}
