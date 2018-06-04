<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\ProcessStatusFree as Query;

class ProcessFree extends BaseController
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
        $keepLessData = Validator::param('keepLessData')->isArray()->setDefault([])->getValue();
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
            ->readFreeProcesses($calendar, \App::getNow(), $slotType, $slotsRequired, $groupData ? true: false)
            ->withLessData($keepLessData)
        ;
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
