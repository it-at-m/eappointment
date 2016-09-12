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
class Counter extends BaseController
{
    /**
     * @return String
     */
    public function __invoke(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $provider = \App::$http->readGetResult(
            '/provider/dldb/'. $this->workstation->getProviderOfGivenScope() .'/'
        )->getEntity();
        $requestList = \App::$http->readGetResult('/request/dldb/provider/'. $provider->id .'/')->getCollection();

        if (!$this->workstation->hasId()) {
            return \BO\Slim\Render::redirect(

                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/counter.twig',
            array(
                'title' => 'Tresen',
                'menuActive' => 'counter',
                'workstation' => $this->workstation->getArrayCopy(),
                'requestList' => $requestList->sortByName()
            )
        );
    }
}
