<?php

namespace BO\Zmsadmin\Helper;

class MailTemplateArrayProvider
{
    protected $templates = array();

    public function __construct()
    {
    }

    public function getTemplate($templateName)
    {
        return $this->templates[$templateName];
    }

    public function getTemplates()
    {
        return $this->templates;
    }

    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }
}
