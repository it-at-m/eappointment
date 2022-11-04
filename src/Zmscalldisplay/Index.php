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
    static $hashParameter = ['webcalldisplay' => ['collections', 'queue']];
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
        $parameters = $this->getHashedWebcalldiplayUrl($request, $parameters);

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

    protected function getHashedWebcalldiplayUrl(RequestInterface $request, array $parameters)
    {
        $config = \App::$http->readGetResult('/config/')->getEntity();
        if ($request->getQueryParam('qrcode') &&
            $request->getQueryParam('qrcode') == 1 &&
            ($request->getQueryParam('collections') || $request->getQueryParam('queue'))
        ) {
            $parameters['showQrCode'] = true;
            $parameters['webcalldisplayUrl'] = '';
            $parameters['webcalldisplayUrl'] .= $config->toProperty()->webcalldisplay->baseUrl->get();
            $parameters['webcalldisplayUrl'] .= '?'. $this->buildQuery($request);
            

        }
        return $parameters;
    }

    protected function buildQuery($request)
    {
        $parameter['collections'] = $request->getQueryParam('collections');
        if ($request->getQueryParam('queue')) {
            $parameter['queue'] = $request->getQueryParam('queue');
        }
        if ($request->getQueryParam('template')) {
            $parameter['template'] = $request->getQueryParam('template');
        }
        $hash = $this->buildHashFromParameterList($request, static::$hashParameter);
        $parameter['hmac'] = $hash;
        return http_build_query($parameter);
    }

    protected function buildHashFromParameterList($request, $list)
    {
        $firstKey = array_key_first($list);
        $paramsToHash = [];
        foreach(array_values($list[$firstKey]) as $parameter) {
            if ($request->getQueryParam($parameter)) {
                $paramsToHash[$parameter] = $request->getQueryParam($parameter);
            }
        }
        $hash = SlimHelper::hashQueryParameters(
            $firstKey,
            $paramsToHash,
            $list[$firstKey]
        );
        return $hash;
    }
}
