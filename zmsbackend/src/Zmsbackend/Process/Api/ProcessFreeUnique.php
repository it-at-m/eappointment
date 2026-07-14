<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\ProcessStatusFree as Query;

class ProcessFreeUnique extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $groupData = Validator::param('groupData')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        $calendarData = Validator::input()->isJson()->assertValid()->getValue();
        $calendar = new \BO\Zmsentities\Calendar($calendarData);
        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $processList = (new Query())
            ->readFreeProcessesMinimalDeduplicated($calendar, \App::getNow(), $slotType, $slotsRequired, $groupData ? true : false);

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
