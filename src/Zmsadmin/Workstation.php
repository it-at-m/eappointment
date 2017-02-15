<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

/**
  * Handle requests concerning services
  *
  */
class Workstation extends BaseController
{
    /**
     * @return String
     */
    public function invokeHook(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $provider = \App::$http->readGetResult(
            '/provider/dldb/'. $workstation->getProviderOfGivenScope() .'/'
        )->getEntity();
        $requestList = \App::$http->readGetResult('/request/dldb/provider/'. $provider->id .'/')->getCollection();

        if (!$workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstation.twig',
            array(
                'title' => 'Sachbearbeiter',
                'menuActive' => 'workstation',
                'workstation' => $workstation,
                'requestList' => $requestList->sortByName()
            )
        );
    }
}
