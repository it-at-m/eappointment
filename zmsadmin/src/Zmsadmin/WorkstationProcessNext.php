<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;
use BO\Zmsadmin\Helper\ExcludeIds;

class WorkstationProcessNext extends BaseController
{
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ): \Psr\Http\Message\ResponseInterface {
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $validator = $request->getAttribute('validator');
        $excludedIds = ExcludeIds::fromQuery(
            $validator->getParameter('exclude')->isString()->getValue()
        );

        $process = (new Helper\ClusterHelper($workstation))->getNextProcess($excludedIds);

        if (!$process || ! $process->hasId()) {
            return Render::withHtml(
                $response,
                'block/process/next.twig',
                array(
                    'workstation' => $workstation,
                    'processNotFoundInQueue' => 1,
                    'exclude' => ''
                )
            );
        }
        if ($process->toProperty()->amendment->get()) {
            return Render::redirect(
                'workstationProcessPreCall',
                array(
                    'id' => $process->id,
                    'authkey' => $process->authKey
                ),
                array(
                    'exclude' => ExcludeIds::toQuery($excludedIds)
                )
            );
        }
        return Render::redirect(
            'workstationProcessCalled',
            array(
                'id' => $process->id
            ),
            array(
                'exclude' => ExcludeIds::toQuery($excludedIds)
            )
        );
    }
}
