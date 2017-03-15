<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsentities\Helper\DateTime;

/**
  * Handle requests concerning services
  */
class ProcessNextByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId)
    {
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;
        $scope = $query->readEntity($scopeId)->withLessData();
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $queueList = $query->readQueueList($scope->id, $dateTime);
        $process = $queueList->getNextProcess($dateTime);
        if (! $process) {
            throw new Exception\Process\ProcessNotFound();
        }

        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
