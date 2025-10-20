<?php

declare(strict_types=1);

namespace BO\Zmscitizenapi\Utils;

use BO\Zmsentities\Process;
use BO\Zmscitizenapi\Services\Core\LoggerService;
use BO\Zmscitizenapi\Services\Core\ZmsApiClientService;

/**
 * Mail template helper that fetches custom mail templates via API calls.
 * This class loads database-stored mail templates for use in ICS generation and other messaging functions.
 */
class MailTemplateHelper
{
    protected $templates = false;
    protected $process;

    public function __construct(Process $process)
    {
        $this->process = $process;
    }

    /**
     * Get a specific template by name.
     *
     * @param string $templateName
     * @return string|null
     */
    public function getTemplate(string $templateName): ?string
    {
        if (!$this->templates) {
            $this->loadTemplates();
        }
        return $this->templates[$templateName] ?? null;
    }

    /**
     * Get all templates as an array.
     *
     * @return array
     */
    public function getTemplates(): array
    {
        if (!$this->templates) {
            $this->loadTemplates();
        }
        return $this->templates ?? [];
    }

    /**
     * Load templates from the API.
     */
    protected function loadTemplates(): void
    {
        $this->templates = [];
        $providerId = $this->getProviderId();

        if (!$providerId) {
            return;
        }

        $templates = ZmsApiClientService::getMergedMailTemplates($providerId);
        if ($templates) {
            $this->templates = $templates;
        }
    }

    /**
     * Get the provider ID from the process.
     *
     * @return int|null
     */
    protected function getProviderId(): ?int
    {
        if (!isset($this->process->scope) || !isset($this->process->scope->provider)) {
            return null;
        }

        $provider = $this->process->scope->provider;
        $providerId = is_object($provider) ? ($provider->id ?? null) : ($provider['id'] ?? null);

        return $providerId !== null ? (int) $providerId : null;
    }
}
