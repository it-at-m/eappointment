<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsentities\Helper\DateTime;

class ProcessNextByScope extends BaseController
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
        (new Helper\User($request))->checkRights();
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $exclude = Validator::param('exclude')->isString()->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        $scope = $query->readEntity($args['id']);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $queueList = $query->readQueueList($scope->id, $dateTime, 1);
        
        $message = Response\Message::create($request);
        $message->data = static::getProcess($queueList, $dateTime, $exclude);
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }

    public static function getProcess($queueList, $dateTime, $exclude = null)
    {
        $process = $queueList->getNextProcess($dateTime, $exclude);
        return ($process) ? $process : new \BO\Zmsentities\Process();
    }
}
