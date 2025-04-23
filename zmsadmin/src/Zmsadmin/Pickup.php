<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

class Pickup extends BaseController
{
    public static $defaultLimit = 500;

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
        $validator = $request->getAttribute('validator');
        $selectedProcess = $validator->getParameter('selectedprocess')->isString()->getValue();
        $limit = $validator->getParameter('limit')->isNumber()->setDefault(static::$defaultLimit)->getValue();
        $offset = $validator->getParameter('offset')->isNumber()->setDefault(0)->getValue();

        return \BO\Slim\Render::withHtml(
            $response,
            'page/pickup.twig',
            array(
              'title' => 'Abholer verwalten',
              'workstation' => $workstation,
              'menuActive' => 'pickup',
              'source' => 'pickup',
              'limit' => $limit,
              'offset' => $offset,
              'selectedScope' => $workstation->scope['id'],
              'selectedProcess' => ($workstation->process->hasId()) ? $workstation->process->getId() : $selectedProcess
            )
        );
    }
}
