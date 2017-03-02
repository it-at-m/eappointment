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
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        $provider = \App::$http->readGetResult(
            '/provider/dldb/'. $workstation->getProviderOfGivenScope() .'/'
        )->getEntity();
        $requestList = \App::$http->readGetResult('/request/dldb/provider/'. $provider->id .'/')->getCollection();

        $validator = $request->getAttribute('validator');
        $date = $validator->getParameter('date')->isString()->getValue();

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
            'page/counter.twig',
            array(
                'title' => 'Tresen',
                'menuActive' => 'counter',
                'date' => $date,
                'workstation' => $workstation->getArrayCopy(),
                'requestList' => $requestList->sortByName()
            )
        );
    }
}
