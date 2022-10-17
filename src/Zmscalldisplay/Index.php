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
            $uri = $request->getUri();
            $parameters['showQrCode'] = true;
            $parameters['webcalldisplayUrl'] = $uri->getScheme() . '://'. $uri->getHost();
            $parameters['webcalldisplayUrl'] .= $uri->getPort() ? ':' . $uri->getPort() : '';
            $parameters['webcalldisplayUrl'] .= \App::$webcalldisplayUrl;
            $parameters['webcalldisplayUrl'] .= str_replace('/&', '/?', $uri->getQuery());
            $parameters['webcalldisplayUrl'] .= '&hmac=' . SlimHelper::hashQueryParameters(
                'webcalldisplay',
                ['collections' => $request->getQueryParam('collections'), 'queue' => $request->getQueryParam('queue')],
                ['collections', 'queue']
            );
            $parameters['webcalldisplayUrl'] = str_replace(
                '&qrcode=' . $request->getQueryParam('qrcode'),
                '',
                $parameters['webcalldisplayUrl']
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
