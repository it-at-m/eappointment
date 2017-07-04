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
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        if (! $workstation->hasId()) {
            return \BO\Slim\Render::redirect(
                'index',
                array(
                    'error' => 'login_failed'
                )
            );
        }
        $provider = \App::$http->readGetResult(
            '/provider/dldb/'. $workstation->getProviderOfGivenScope() .'/'
        )->getEntity();
        $requestList = \App::$http->readGetResult('/provider/dldb/'. $provider->id .'/request/')->getCollection();

        $validator = $request->getAttribute('validator');
        $selectedDate = $validator->getParameter('date')->isString()->getValue();
        $selectedDate = ($selectedDate) ? $selectedDate : \App::$now->format('Y-m-d');
        $selectedTime = $validator->getParameter('time')->isString()->getValue();
        $selectedTime = ($selectedTime) ? $selectedTime : null;
        $selectedProcessId = $validator->getParameter('selectedprocess')->isNumber()->getValue();
        $calledProcessId = $validator->getParameter('calledprocess')->isNumber()->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/workstation.twig',
            array(
                'title' => 'Sachbearbeiter',
                'menuActive' => 'workstation',
                'workstation' => $workstation,
                'selectedDate' => $selectedDate,
                'selectedTime' => $selectedTime,
                'selectedProcess' => $selectedProcessId,
                'calledProcess' => $calledProcessId,
                'requestList' => $requestList->sortByName()
            )
        );
    }
}
