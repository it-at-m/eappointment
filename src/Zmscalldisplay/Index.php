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
        $parameters = $this->getDefaultParamters($request, $calldisplayHelper);
        $parameters = $this->getHashedUrlParameter($request, $parameters);

        $calldisplay = $calldisplayHelper->getEntity();

        $template = (new Helper\TemplateFinder($defaultTemplate))->setCustomizedTemplate($calldisplay);
        return \BO\Slim\Render::withHtml(
            $response,
            $template->getTemplate(),
            $parameters
        );
    }

    protected function getDefaultParamters(RequestInterface $request, $calldisplayHelper)
    {
        $calldisplay = $calldisplayHelper->getEntity();
        return [
            'debug' => \App::DEBUG,
            'queueStatusRequested' => implode(',', $calldisplayHelper::getRequestedQueueStatus($request)),
            'scopeList' => $calldisplay->getFullScopeList()->getIdsCsv(),
            'title' => 'Aufrufanzeige',
            'calldisplay' => $calldisplay,
            'showQrCode' => false,
        ];
    }

    protected function getHashedUrlParameter(RequestInterface $request, array $parameters)
    {
        $config = \App::$http->readGetResult('/config/')->getEntity();
        if ($request->getQueryParam('qrcode') &&
            $request->getQueryParam('qrcode') == 1 &&
            ($request->getQueryParam('collections') || $request->getQueryParam('queue'))
        ) {
            $uri = $request->getUri();
            $parameters['showQrCode'] = true;
            $parameters['webcalldisplayUrl'] = '';
            $parameters['webcalldisplayUrl'] .= $config->toProperty()->webcalldisplay->baseUrl->get();
            $parameters['webcalldisplayUrl'] .= str_replace('/&', '/?', $request->getUri()->getQuery());
            $parameters['webcalldisplayUrl'] .= '&hmac=' . SlimHelper::hashQueryParameters(
                'webcalldisplay',
                [
                    'collections' => $request->getQueryParam('collections'),
                    'queue' => $request->getQueryParam('queue')
                ],
                [   'collections',
                    'queue'
                ]
            );
            $parameters['webcalldisplayUrl'] = str_replace(
                '&qrcode=' . $request->getQueryParam('qrcode'),
                '',
                $parameters['webcalldisplayUrl']
            );
        }
        return $parameters;
    }
}
