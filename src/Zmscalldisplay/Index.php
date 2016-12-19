<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

/**
 * Handle requests concerning services
 */
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
        $calldisplayHelper = (new Helper\Calldisplay($request));
        $calldisplay = $calldisplayHelper->getEntity();

        $template = Helper\TemplateFinder::getCustomizedTemplate($calldisplay);
        return \BO\Slim\Render::withHtml(
            $response,
            $template,
            array(
                'debug' => \App::DEBUG,
                'queueStatusRequested' => $calldisplayHelper::getRequestedQueueStatus($request),
                'collections' => $calldisplayHelper->collections,
                'title' => 'Aufrufanzeige',
                'calldisplay' => $calldisplay,
            )
        );
    }
}
