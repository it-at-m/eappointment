<?php

/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */

namespace BO\Zmsticketprinter;

use BO\Mellon\Unvalidated;
use BO\Mellon\Valid;
use BO\Mellon\Validator;
use BO\Slim\Render;
use BO\Zmsentities\Ticketprinter;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Index extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    public function readResponse(RequestInterface $request, ResponseInterface $response, array $args)
    {
        Helper\HomeUrl::create($request);
        $config = $this->getConfig();

        $validator = $request->getAttribute('validator');
        $defaultTemplate = $this->getDefaultTemplate($validator);
        $languageConfig = $this->getLanguageConfig($validator);

        $currentLang = $this->getCurrentLanguage($validator);
        $queryString = $this->getQueryStringWithLang();

        $translations = $this->getTranslations($languageConfig, $currentLang);
        $defaultLanguage = $languageConfig['defaultLanguage'] ?? 'de';
        $languages = $this->getAvailableLanguages($languageConfig);

        $ticketprinterHelper = new Helper\Ticketprinter($args, $request);
        $ticketprinter = $ticketprinterHelper->getEntity();

        $ticketprinter->testValid();
        $scope = $ticketprinter->getScopeList()->getFirst();
        $department = $this->getDepartment($scope);
        $organisation = $ticketprinterHelper->getOrganisation();

        if ($this->shouldRedirectToScope($ticketprinter)) {
            return Render::redirect(
                'TicketprinterByScope',
                ['scopeId' => $ticketprinter->buttons[0]['scope']['id']],
                $this->getQueryString($validator, $ticketprinter, $defaultTemplate)
            );
        }

        $template = (new Helper\TemplateFinder($defaultTemplate->getValue()))
            ->setCustomizedTemplate($ticketprinter, $organisation);

        return Render::withHtml(
            $response,
            $template->getTemplate(),
            [
                'debug' => \App::DEBUG,
                'refreshInSeconds' => 30,
                'urlQueryString' => $queryString,
                'currentLang' => $currentLang,
                'enabled' => $ticketprinter->isEnabled()
                    || !$organisation->getPreference('ticketPrinterProtectionEnabled'),
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'department' => $department,
                'buttonDisplay' => $template->getButtonTemplateType($ticketprinter),
                'config' => $config,
                'defaultLanguage' => $defaultLanguage,
                'languages' => $languages,
                'translations' => $translations,
                'hasDisabledButton' => $this->hasDisabledButton($ticketprinter)
            ]
        );
    }

    private function getConfig()
    {
        return \App::$http->readGetResult('/config/', [], \App::SECURE_TOKEN)->getEntity();
    }

    private function getDefaultTemplate($validator)
    {
        return $validator->getParameter("template")
            ->isPath()
            ->setDefault('default');
    }

    private function getLanguageConfig($validator)
    {
        $config = $validator->getParameter("config")
            ->isString()
            ->getValue();

        if ($config === null) {
            return [];
        }

        $config = (string)$config;
        $decoded = base64_decode(str_replace(' ', '+', $config));

        $result = json_decode($decoded, true);
        return $result !== null ? $result : [];
    }

    private function getCurrentLanguage($validator)
    {
        return $validator->getParameter("lang")->isString()->getValue();
    }

    private function getQueryStringWithLang()
    {
        // Ensure we have a string value, defaulting to empty string if null or not set
        $queryString = isset($_SERVER['QUERY_STRING']) ? (string)$_SERVER['QUERY_STRING'] : '';
        
        // Only append lang parameter if we have a non-empty string and it doesn't already contain lang
        if ($queryString !== '' && strpos($queryString, 'lang=') === false) {
            $queryString .= '&lang=de';
        } elseif ($queryString === '') {
            $queryString = 'lang=de';
        }
        
        // Ensure we're working with a string before replacement
        return str_replace('/&', '', $queryString);
    }

    private function getTranslations($languageConfig, $currentLang)
    {
        $translations = ['printText' => ''];
        if ($languageConfig) {
            foreach ($languageConfig['languages'] as $language) {
                if ($language['language'] !== $currentLang) {
                    continue;
                }
                foreach ($language['translations'] as $requestId => $translation) {
                    $translations[$requestId] = $translation;
                }
            }
            if (empty($currentLang) || $currentLang === 'de') {
                $translations['printText'] = $languageConfig['defaultPrintText'] ?? '';
            }
        }
        return $translations;
    }

    private function getAvailableLanguages($languageConfig)
    {
        return array_column($languageConfig['languages'] ?? [], 'language');
    }

    private function getDepartment($scope)
    {
        return \App::$http->readGetResult('/scope/' . $scope->id . '/department/')->getEntity();
    }

    private function shouldRedirectToScope($ticketprinter)
    {
        return count($ticketprinter->buttons) === 1 && $ticketprinter->buttons[0]['type'] === 'scope';
    }

    private function hasDisabledButton($ticketprinter)
    {
        foreach ($ticketprinter->buttons as $button) {
            if (!isset($button['enabled']) || $button['enabled'] != 1) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Validator $validator
     * @param Ticketprinter $ticketprinter
     * @param Unvalidated|Valid $defaultTemplate
     * @return array
     */
    protected function getQueryString($validator, $ticketprinter, $defaultTemplate)
    {
        $query = ($defaultTemplate->getValue() === 'default') ? [] : ['template' => $defaultTemplate->getValue()];
        if (isset($ticketprinter['home'])) {
            $homeUrl = $validator::value($ticketprinter['home'])->isUrl()->getValue();
            if ($homeUrl) {
                $query['ticketprinter[home]'] = $homeUrl;
            }
        }
        return $query;
    }
}
