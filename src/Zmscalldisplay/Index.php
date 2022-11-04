<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

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
        if ($request->getQueryParam('qrcode') && $request->getQueryParam('qrcode') == 1) {
            $parameters['showQrCode'] = true;
            $parameters = $this->getHashedUrl($request, $parameters);
        }
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
}
