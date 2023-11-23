<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsentities\Collection\ProcessList;

class WorkstationProcessNext extends BaseController
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
        $workstation = \App::$http->readGetResult('/workstation/', [
            'resolveReferences' => 1,
            'gql' => Helper\GraphDefaults::getWorkstation()
        ])->getEntity();
        $validator = $request->getAttribute('validator');
        $excludedIds = $validator->getParameter('exclude')->isString()->getValue();
        $excludedIds = ($excludedIds) ? $excludedIds : '';

        function timeToUnix($timeString)
        {
            list($hours, $minutes, $seconds) = explode(':', $timeString);
            return mktime($hours, $minutes, $seconds);
        }

        $selectedDateTime = \App::$now;
        $selectedDateTime = ($selectedDateTime < \App::$now) ? \App::$now : $selectedDateTime;

        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);

        $processList = $workstationRequest->readProcessListByDate(
            $selectedDateTime,
            Helper\GraphDefaults::getProcess()
        );


$filteredProcessList = new ProcessList;

foreach ($processList as $process) {
    if ($process->status === "queued") {
        $queuedTimeUnix = isset($process->queuedTime) ? timeToUnix($process->queuedTime) : null;
        $currentTimeUnix = time();

        // Check if callCount is 0 or queuedTime is more than five minutes ago
        if(!isset($process->queuedTime)){
            $filteredProcessList->addEntity(clone $process);
        } else if (isset($queuedTimeUnix) && !($process->queue->callCount > 0 && ($currentTimeUnix - $queuedTimeUnix) < 300)) {
            // Add the process to the filtered list
            $filteredProcessList->addEntity(clone $process);
        } else {
            
            if (!empty($excludedIds)) {
                // Add a comma before appending if $excludedIds is not empty
                $excludedIds .= ",";
            }
        
            $excludedIds .= $process->queue->number;
        }
    }
}


    
        $process = isset($filteredProcessList[0]) ? $filteredProcessList[0] : null;



        $process = (new Helper\ClusterHelper($workstation))->getNextProcess($excludedIds);




        

        /*
        do {
            //error_log("Huh");
            $process = (new Helper\ClusterHelper($workstation))->getNextProcess($excludedIds);
        
            // Log the process details
            //error_log(json_encode($process->queue->number));
            error_log($excludedIds);
            

            
            if ($process->queue->number !== 0) {
                $queuedTimeUnix = timeToUnix($process->queuedTime);
                $currentTimeUnix = time();
            }


            // Check if callCount is greater than 0 and queued time is less than five minutes ago


            $fetchAgain = $process->queue->callCount > 0 && ($currentTimeUnix - $queuedTimeUnix) < 300;

            //$fetchAgain = false;

            if ($fetchAgain) {
                if (!empty($excludedIds)) {
                    $excludedIds .= ",";
                }
                $excludedIds .= $process->queue->number;
            }
        } while ($fetchAgain);
        */
              

        if (! $process->hasId() || $process->getFirstAppointment()->date > \App::$now->getTimestamp()) {
            return \BO\Slim\Render::withHtml(
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
            return \BO\Slim\Render::redirect(
                'workstationProcessPreCall',
                array(
                    'id' => $process->id,
                    'authkey' => $process->authKey
                ),
                array(
                    'exclude' => $excludedIds
                )
            );
        }
        return \BO\Slim\Render::redirect(
            'workstationProcessCalled',
            array(
                'id' => $process->id
            ),
            array(
                'exclude' => $excludedIds
            )
        );
    }
}
