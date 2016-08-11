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

        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $provider = \App::$http->readGetResult(
            '/provider/dldb/'. $workstation->getProviderOfGivenScope() .'/'
        )->getEntity();
        $requestList = \App::$http->readGetResult('/request/dldb/provider/'. $provider->id .'/')->getCollection();

        if (!$workstation->hasId()) {
            return Helper\Render::checkedRedirect(
                self::$errorHandler,
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }

        return Helper\Render::checkedHtml(
            self::$errorHandler,
            $response,
            'page/counter.twig',
            array(
                'title' => 'Tresen',
                'menuActive' => 'counter',
                'workstation' => $workstation->getArrayCopy(),
                'requestList' => $requestList->sortByName()
            )
        );
    }
}
