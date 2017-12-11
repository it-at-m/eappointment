<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsstatistic;

class Overview extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 3])->getEntity();
        $department = \App::$http->readGetResult('/scope/' . $workstation->scope['id'] . '/department/')->getEntity();
        $organisation = \App::$http->readGetResult('/department/' .$department->id . '/organisation/')->getEntity();

        $waitingperiod = \App::$http
          ->readGetResult('/warehouse/waitingscope/' . $workstation->scope['id'] . '/')
          ->getEntity();
        $clientperiod = \App::$http
          ->readGetResult('/warehouse/clientscope/' . $workstation->scope['id'] . '/')
          ->getEntity();

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
            'page/overview.twig',
            array(
                'title' => 'Statistik',
                'workstation' => $workstation->getArrayCopy(),
                'department' => $department,
                'organisation' => $organisation,
                'waitingperiod' => $waitingperiod,
                'clientperiod' => $clientperiod,
                'isOverview' => 1
            )
        );
    }
}
