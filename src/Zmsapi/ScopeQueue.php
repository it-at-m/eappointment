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
class ScopeQueue extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $selectedDate = Validator::param('date')->isString()->getValue();
        $dateTime = ($selectedDate) ? new DateTime($selectedDate) : \App::$now;

        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = $query->readEntity($itemId, $resolveReferences)->withLessData();
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }
        $scope = $query->readWithWorkstationCount($itemId, $dateTime);
        $queueList = $query->readQueueListWithWaitingTime($scope, $dateTime)->withPickupDestination($scope);

        $message = Response\Message::create(Render::$request);
        $message->data = $queueList;

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
