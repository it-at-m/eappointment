<?php
/**
 *
 * @package Zmsticketprinter
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsticketprinter;

class Index extends BaseController
{

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        Helper\HomeUrl::create($request);
        $config = \App::$http->readGetResult('/config/')->getEntity();
        $validator = $request->getAttribute('validator');
        $defaultTemplate = $validator->getParameter("template")
            ->isPath()
            ->setDefault('default');
        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();
        $ticketprinter->testValid();

        $organisation = $ticketprinterHelper::$organisation;
        if (1 == count($ticketprinter->buttons) && 'scope' == $ticketprinter->buttons[0]['type']) {
            return \BO\Slim\Render::redirect(
                'TicketprinterByScope',
                array(
                    'scopeId' => $ticketprinter->buttons[0]['scope']['id']
                ),
                $this->getQueryString($validator, $ticketprinter, $defaultTemplate)
            );
        }
        $template = (new Helper\TemplateFinder($defaultTemplate->getValue()))
            ->setCustomizedTemplate($ticketprinter, $organisation);

        return \BO\Slim\Render::withHtml(
            $response,
            $template->getTemplate(),
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'buttonDisplay' => $template->getButtonTemplateType($ticketprinter),
                'config' => $config
            )
        );
    }

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
