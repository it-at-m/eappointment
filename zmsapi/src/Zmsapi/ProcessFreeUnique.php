<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsdb\ProcessStatusFree as Query;

class ProcessFreeUnique extends BaseController
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
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $groupData = Validator::param('groupData')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new Helper\User($request))->checkRights();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        $calendarData = Validator::input()->isJson()->assertValid()->getValue();
        $calendar = new \BO\Zmsentities\Calendar($calendarData);
        $message = Response\Message::create($request);
        $processList = (new Query())
            ->readFreeProcesses($calendar, \App::getNow(), $slotType, $slotsRequired, $groupData ? true : false);

        // Deduplicate processes with same provider (office) and appointment date
        $uniqueProcesses = [];
        foreach ($processList as $process) {
            $appointment = $process->appointments->getFirst();
            $providerId = isset($process->scope->provider->id) ? $process->scope->provider->id : null;
            if ($appointment && $providerId) {
                $key = $providerId . '_' . $appointment->date;
                if (!isset($uniqueProcesses[$key])) {
                    $uniqueProcesses[$key] = $process;
                }
            }
        }
        $processList = new \BO\Zmsentities\Collection\ProcessList(array_values($uniqueProcesses));

        if ($groupData && count($processList) >= $groupData) {
            $processList = $processList->withUniqueScope(true);
        } elseif ($groupData) {
            $processList = $processList->withUniqueScope(false);
        }
        $message->data = $processList;

        $response = Render::withLastModified($response, time(), '0');
        return Render::withJson($response, $message, 200);
    }
}
