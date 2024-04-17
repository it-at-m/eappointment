<?php
/**
 *
 * @package Zmscalldisplay
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use BO\Slim\Request as SlimRequest;

/**
 * Handle requests concerning services
 */
class Index extends BaseController
{
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param RequestInterface|SlimRequest $request
     * @param ResponseInterface $response
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');
        $defaultTemplate = $validator->getParameter("template")
            ->isPath()
            ->setDefault('default_platz')
            ->getValue();

        if ($request->getParam('zoom')) {
            $parameters['zoom'] = (float) $request->getParam('zoom');
        }

        $calldisplayHelper = (new Helper\Calldisplay($request));
        $parameters = $this->getDefaultParamters($request, $calldisplayHelper);
        if ($request->getParam('qrcode') && $request->getParam('qrcode') == 1) {
            $parameters['showQrCode'] = true;
            $parameters['webcalldisplay'] = $this->getWebcallDisplayUrl($request, $parameters);
        }
        $calldisplay = $calldisplayHelper->getEntity();

        $template = (new Helper\TemplateFinder($defaultTemplate))->setCustomizedTemplate($calldisplay);
        $parameters['displayNumber'] = $request->getParam('display') ?? null;

        return \BO\Slim\Render::withHtml(
            $response,
            $template->getTemplate(),
            $parameters
        );
    }

    /**
     * @param RequestInterface $request
     * @param Helper\Calldisplay $calldisplayHelper
     * @return array
     */
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
