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
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        Helper\HomeUrl::create($request);
        $config = \App::$http->readGetResult('/config/', [], \App::SECURE_TOKEN)->getEntity();
        $validator = $request->getAttribute('validator');
        $defaultTemplate = $validator->getParameter("template")
            ->isPath()
            ->setDefault('default');
        $languageConfig = str_replace(' ', '+', $validator->getParameter("config")->isString()->getValue());
        $languageConfig = json_decode(base64_decode($languageConfig), true);
        $currentLang = $validator->getParameter("lang")->isString()->getValue();
        $queryString = isset($_SERVER['QUERY_STRING']) ? str_replace('/&', '', $_SERVER['QUERY_STRING']) : '';

        if (! strpos($queryString, 'lang=')) {
            $queryString .= '&lang=de';
            $currentLang = 'de';
        }

        $translations = [
            'printText' => ''
        ];
        $languages = [];
        $defaultLanguage = 'de';

        if ($languageConfig) {
            $defaultLanguage = $languageConfig['defaultLanguage'] ?? '';
            foreach ($languageConfig['languages'] as $language) {
                $languages[] = $language['language'];

                if ($language['language'] !== $currentLang) {
                    continue;
                }

                foreach ($language['translations'] as $requestId => $translation) {
                    $translations[$requestId] = $translation;
                }
            }
        }

        if (empty($currentLang) || $currentLang === 'de') {
            $translations['printText'] = $languageConfig['defaultPrintText'] ?? '';
        }

        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();
        $ticketprinter->testValid();
        $scope = $ticketprinter->getScopeList()->getFirst();
        $department = \App::$http->readGetResult('/scope/'. $scope->id . '/department/')->getEntity();
        $organisation = $ticketprinterHelper->getOrganisation();


        /*
         *Check whether at least one button is not active (no opening hours stored or location deactivated)
         *the value will be transferd to the template.
        */
        $hasDisabledButton = false;
        foreach ($ticketprinter->buttons as $button) {
            if (!isset($button['enabled']) || $button['enabled'] != 1) {
                $hasDisabledButton = true;
                break;
            }
        }

        if (1 == count($ticketprinter->buttons) && 'scope' == $ticketprinter->buttons[0]['type']) {
            return Render::redirect(
                'TicketprinterByScope',
                array(
                    'scopeId' => $ticketprinter->buttons[0]['scope']['id']
                ),
                $this->getQueryString($validator, $ticketprinter, $defaultTemplate)
            );
        }
        $template = (new Helper\TemplateFinder($defaultTemplate->getValue()))
            ->setCustomizedTemplate($ticketprinter, $organisation);

        return Render::withHtml(
            $response,
            $template->getTemplate(),
            array(
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
                'hasDisabledButton' => $hasDisabledButton
            )
        );
    }

    /**
     * @param Validator $validator
     * @param Ticketprinter $ticketprinter
     * @param Unvalidated|Valid $defaultTemplate
     * @return array
     */
    protected function getQueryString($validator, $ticketprinter, $defaultTemplate)
    {
        $query = ($defaultTemplate->getValue() == 'default') ? [] : ['template' => $defaultTemplate->getValue()];
        if (isset($ticketprinter['home'])) {
            $homeUrl = $validator::value($ticketprinter['home'])->isUrl()->getValue();
            if ($homeUrl) {
                $query['ticketprinter[home]'] = $homeUrl;
            }
        }

        return $query;
    }
}
