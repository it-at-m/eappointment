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
        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();
        $ticketprinter->testValid();
        $scope = $ticketprinter->getScopeList()->getFirst();
        $department = \App::$http->readGetResult('/scope/'. $scope->id . '/department/')->getEntity();
        $organisation = $ticketprinterHelper->getOrganisation();

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
                'enabled' => $ticketprinter->isEnabled()
                    || !$organisation->getPreference('ticketPrinterProtectionEnabled'),
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'department' => $department,
                'buttonDisplay' => $template->getButtonTemplateType($ticketprinter),
                'config' => $config
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
