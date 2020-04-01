<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic;

/**
 * Handle requests concerning services
 */
class Status extends BaseController
{
    protected $withAccess = false;

    //protected $resolveLevel = 3;
    
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $result = \App::$http->readGetResult('/status/');
        return \BO\Slim\Render::withHtml(
            $response,
            'page/status.twig',
            array(
                'title' => 'Status der Terminvereinbarung',
                //'workstation' => $this->workstation->getArrayCopy(),
                'status' => $result->getEntity(),
            )
        );
    }
}
