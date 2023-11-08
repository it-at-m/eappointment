<?php
/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic;

use BO\Slim\Render;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Handle requests concerning services
 */
class Status extends BaseController
{
    protected $withAccess = false;

    //protected $resolveLevel = 3;
    
    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @return ResponseInterface
     */
    public function readResponse(
        RequestInterface $request,
        ResponseInterface $response,
        array $args
    ) {
        $result = \App::$http->readGetResult('/status/');
        return Render::withHtml(
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
