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
            throw new \BO\Zmsticketprinter\Exception\TemplateNotFound("Could not find template $this->template");
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
    public function setCustomizedTemplate($ticketprinter, $organisation)
    {
        $template = null;
        if ($this->defaultTemplate == 'default') {
            $template = $this->getTemplateBySettings($ticketprinter, $organisation);
        }
        $this->template = ($template) ? $template : $this->template;
        return $this;
    }

    public function getButtonTemplateType($ticketprinter)
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

    protected function getTemplateBySettings($ticketprinter, $organisation)
    {
        $template = null;
        //look for customized templates by single scope or single cluster
        if (1 == count($ticketprinter->buttons)) {
            $entity = null;
            if ($ticketprinter->getScopeList()->getFirst()) {
                $entity = $ticketprinter->getScopeList()->getFirst();
            } elseif ($ticketprinter->getClusterList()->getFirst()) {
                $entity = $ticketprinter->getClusterList()->getFirst();
            }
            $template = $this->getExistingTemplate($entity);
        }
        //look for customized template in clusterlist, overwrite template before
        foreach ($ticketprinter->getClusterList() as $entity) {
            if ($this->getExistingTemplate($entity)) {
                $template = $this->getExistingTemplate($entity);
                break;
            }
        }
        //look for customized template in departmentlist, overwrite template before
        foreach ($organisation->departments as $departmentData) {
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
        $path = $this->subPath .'/buttonDisplay_'. $entity->getEntityName() .'_'. $entity->id .'.twig';
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
