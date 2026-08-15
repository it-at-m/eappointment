<?php

namespace BO\Zmsadmin\Helper;

class MailTemplateArrayProvider
{
    protected $templates = array();

    public function __construct()
    {
    }

    /** @psalm-api */
    public function getTemplate($templateName)
    {
        return $this->templates[$templateName];
    }

    /** @psalm-api */
    public function getTemplates()
    {
        return $this->templates;
    }

    public function setTemplates(array $templates): void
    {
        $this->templates = $templates;
    }
}
