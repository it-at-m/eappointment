<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class PickupHandheld extends PickupQueue
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 1])->getEntity();
        $selectedProcess = null;
        if ($request->getMethod() === 'POST') {
            $input = $request->getParsedBody();
            $selectedProcess = $input['inputNumber'];
        }
        if ($workstation->process && $workstation->process->hasId()) {
            $selectedProcess = $workstation->process['id'];
        }

        return \BO\Slim\Render::withHtml(
            $response,
            'page/pickupHandheld.twig',
            array(
              'title' => 'Abholer verwalten',
              'workstation' => $workstation->getArrayCopy(),
              'menuActive' => 'pickup',
              'selectedProcess' => $selectedProcess
            )
        );
    }
}
