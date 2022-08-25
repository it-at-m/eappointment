<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

use BO\Slim\Helper as SlimHelper;
use \Psr\Http\Message\RequestInterface;
use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Request as SlimRequest;

/**
 * Handle requests concerning services
 */
class Index extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param RequestInterface|SlimRequest
     * @return String
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $defaultTemplate = $validator->getParameter("template")
            ->isPath()
            ->setDefault('defaultplatz')
            ->getValue();
        $calldisplayHelper = (new Helper\Calldisplay($request));
        $calldisplay = $calldisplayHelper->getEntity();
        $parameters  = [
            'debug' => \App::DEBUG,
            'queueStatusRequested' => implode(',', $calldisplayHelper::getRequestedQueueStatus($request)),
            'scopeList' => $calldisplay->getFullScopeList()->getIdsCsv(),
            'title' => 'Aufrufanzeige',
            'calldisplay' => $calldisplay,
            'showQrCode' => false,
        ];

        if ($request->getQueryParam('qrcode') && $request->getQueryParam('qrcode') == 1
            && ($request->getQueryParam('collections') || $request->getQueryParam('queue'))
        ) {
            $parameters['showQrCode'] = true;
            $parameters['webcalldisplayUrl'] = $request->getUri()->getScheme() . '://'. $request->getUri()->getHost();
            $parameters['webcalldisplayUrl'] .= '/terminvereinbarung/aufruf';
            $parameters['webcalldisplayUrl'] .= urldecode(str_replace('/&', '/?', $request->getUri()->getQuery()));
            $parameters['webcalldisplayUrl'] .= '&hmac=' . SlimHelper::hashQueryParameters(
                ['collections' => $request->getQueryParam('collections'), 'queue' => $request->getQueryParam('queue')],
                ['collections', 'queue']
            );
        }

        $template = (new Helper\TemplateFinder($defaultTemplate))->setCustomizedTemplate($calldisplay);
        return \BO\Slim\Render::withHtml(
            $response,
            $template->getTemplate(),
            $parameters
        );
    }
}
