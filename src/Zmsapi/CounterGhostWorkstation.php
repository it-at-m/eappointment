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
        $input = Validator::input()->isJson()->getValue();
        $scope = new Entity($input);
        if ($scope->id != $workstation->getScope()->id) {
            throw new Exception\Scope\ScopeNoAccess();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = (new Query())->updateGhostWorkstationCount($scope, \App::$now);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
