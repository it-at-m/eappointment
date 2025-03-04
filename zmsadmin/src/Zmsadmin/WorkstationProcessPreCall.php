<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Mellon\Validator;

class WorkstationProcessPreCall extends BaseController
{
    /**
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $validator = $request->getAttribute('validator');

        $workstation = \App::$http->readGetResult('/workstation/', ['resolveReferences' => 2])->getEntity();
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $process = \App::$http->readGetResult('/process/' . $processId . '/')->getEntity();
        $excludedIds = $validator->getParameter('exclude')->isString()->setDefault('')->getValue();
        if ($excludedIds) {
            $exclude = explode(',', $excludedIds);
        }
        $exclude[] = $process->toQueue(\App::$now)->number;

        $error = $validator->getParameter('error')->isString()->getValue();
        if ($workstation->process->getId()) {
            if ($workstation->process->getId() != $processId) {
                $error = 'has_called_process';
            }
            if ('pickup' == $workstation->process->getStatus()) {
                $error = 'has_called_pickup';
            }
        }

        if ('called' == $workstation->process->getStatus()) {
            return \BO\Slim\Render::redirect(
                'workstationProcessCalled',
                ['id' => $workstation->process->getId()],
                ['error' => $error]
            );
        }

        // Get waiting time for precall twig since no query is performed
        $currentTime = new \DateTime();
        $arrivalTime = new \DateTime();
        $timestamp = (int) $process->queue->arrivalTime;
        $arrivalTime->setTimestamp($timestamp);
        $differenceInSeconds = $currentTime->getTimestamp() - $arrivalTime->getTimestamp();
        $hours = intdiv($differenceInSeconds, 3600);
        $minutes = intdiv($differenceInSeconds % 3600, 60);
        $seconds = $differenceInSeconds % 60;
        $waitingTime = sprintf("%02d:%02d:%02d", $hours, $minutes, $seconds);

        return \BO\Slim\Render::withHtml(
            $response,
            'block/process/precall.twig',
            array(
                'title' => 'Sachbearbeiter',
                'workstation' => $workstation,
                'menuActive' => 'workstation',
                'process' => $process,
                'timeDifference' => $waitingTime, // Pass this to Twig
                'exclude' => join(',', $exclude),
                'error' => $error
            )
        );
    }
}
