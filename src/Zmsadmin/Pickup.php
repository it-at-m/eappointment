<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Pickup extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $validator = $request->getAttribute('validator');
        $selectedProcess = $validator->getParameter('selectedprocess')->isString()->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/pickup.twig',
            array(
              'title' => 'Abholer verwalten',
              'workstation' => $workstation,
              'menuActive' => 'pickup',
              'source' => 'pickup',
              'selectedScope' => $workstation->scope['id'],
              'selectedProcess' => ($workstation->process->hasId()) ? $workstation->process->getId() : $selectedProcess
            )
        );
    }
}
