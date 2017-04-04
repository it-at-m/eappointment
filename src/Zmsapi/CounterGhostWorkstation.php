<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsentities\Scope as Entity;

/**
  * Handle requests concerning services
  */
class CounterGhostWorkstation extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $workstation = Helper\User::checkRights('basic');
        $scope = new Entity($workstation->scope);
        $input = Validator::input()->isJson()->getValue();
        $scope->setStatusQueue('ghostWorkstationCount', $input['count']);

        $message = Response\Message::create(Render::$request);
        $message->data = (new Query())->updateGhostWorkstationCount($scope->id, $scope, \App::$now);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
