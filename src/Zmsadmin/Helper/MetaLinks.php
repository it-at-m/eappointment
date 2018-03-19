<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin\Helper;

class MetaLinks extends \BO\Zmsadmin\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $config = \App::$http->readGetResult('/config/')->getEntity();
        return \BO\Slim\Render::withHtml(
            $response,
            'block/metalinks/metalinks.twig',
            array(
                'config' => $config
            )
        );
    }
}
