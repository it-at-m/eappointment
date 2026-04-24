<?php

/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Zmsentities\Collection\ProcessList;

class WorkstationProcessNext extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */

    /**
     * @param string|null $timeString DB/API value: full datetime "Y-m-d H:i:s" or legacy "H:i:s" only
     * @return int|null Unix timestamp in default TZ, or null if empty / unparseable
     */
    public function timeToUnix($timeString)
    {
        if ($timeString === null || $timeString === '') {
            return null;
        }
        $timeString = trim((string) $timeString);
        if ($timeString === '') {
            return null;
        }
        // Full datetime from process.timeoutTime (see zmsdb Query/Process)
        if (preg_match('/^\d{4}-\d{2}-\d{2}[ T]\d{1,2}:\d{2}:\d{2}/', $timeString)) {
            $ts = strtotime($timeString);
            return $ts !== false ? $ts : null;
        }
        // Legacy time-of-day only
        $parts = explode(':', $timeString);
        if (count($parts) >= 2) {
            return mktime(
                (int) $parts[0],
                (int) $parts[1],
                (int) ($parts[2] ?? 0),
                (int) date('n'),
                (int) date('j'),
                (int) date('Y')
            );
        }
        $ts = strtotime($timeString);
        return $ts !== false ? $ts : null;
    }

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

        $selectedDateTime = \App::$now;
        $selectedDateTime = ($selectedDateTime < \App::$now) ? \App::$now : $selectedDateTime;

        $workstationRequest = new \BO\Zmsclient\WorkstationRequests(\App::$http, $workstation);

        $processList = $workstationRequest->readProcessListByDate(
            $selectedDateTime,
            Helper\GraphDefaults::getProcess()
        );

        $filteredProcessList = new ProcessList();

        foreach ($processList as $process) {
            if ($process->status === "queued" || $process->status === "confirmed") {
                $timeoutTimeUnix = isset($process->timeoutTime) ? $this->timeToUnix($process->timeoutTime) : null;
                $currentTimeUnix = time();

                if (!isset($process->timeoutTime)) {
                    $filteredProcessList->addEntity(clone $process);
                } elseif (isset($timeoutTimeUnix) && !($process->queue->callCount > 0 && ($currentTimeUnix - $timeoutTimeUnix) < 300)) {
                    $filteredProcessList->addEntity(clone $process);
                } else {
                    if (!empty($excludedIds)) {
                        $excludedIds .= ",";
                    }
                    $excludedIds .= $process->queue->number;
                }
            }
        }

        $process = (new Helper\ClusterHelper($workstation))->getNextProcess($excludedIds);

        if (!$process || ! $process->hasId() || $process->getFirstAppointment()->date > \App::$now->getTimestamp()) {
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
