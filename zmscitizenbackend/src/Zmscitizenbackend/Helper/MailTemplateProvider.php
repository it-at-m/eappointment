<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Helper;

class MailTemplateProvider
{
    /**
     * @param array<string, string> $templates
     */
    public function __construct(private array $templates)
    {
    }

    /** @psalm-api */
    public function getTemplate(string $templateName): string
    {
        return $this->templates[$templateName];
    }

    /**
     * @return array<string, string>
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
}
