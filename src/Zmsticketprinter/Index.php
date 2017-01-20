<?php
/**
 *
 * @package Zmsappointment
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
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        Helper\HomeUrl::create($request);
        $ticketprinterHelper = (new Helper\Ticketprinter($args, $request));
        $ticketprinter = $ticketprinterHelper->getEntity();
        $ticketprinter->testValid();

        $organisation = $ticketprinterHelper::$organisation;
        if (1 == count($ticketprinter->buttons) && 'scope' == $ticketprinter->buttons[0]['type']) {
             return \BO\Slim\Render::redirect(
                 'TicketprinterByScope',
                 array (
                     'scopeId' => $ticketprinter->buttons[0]['scope']['id']
                 ),
                 array ()
             );
        }
        $template = Helper\TemplateFinder::getCustomizedTemplate($ticketprinter, $organisation);
        return \BO\Slim\Render::withHtml(
            $response,
            $template,
            array(
                'debug' => \App::DEBUG,
                'title' => 'Wartennumer ziehen',
                'ticketprinter' => $ticketprinter,
                'organisation' => $organisation,
                'buttonDisplay' => Helper\TemplateFinder::getButtonTemplateType($ticketprinter)
            )
        );
    }
}
