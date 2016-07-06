<?php
/**
 * @package
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation as Query;

/**
  * Handle requests concerning services
  */
class WorkstationLogin extends BaseController
{
    /**
     * @return String
     */
    public static function render($loginName)
    {
        $query = new Query();
        $input = Validator::input()->isJson()->getValue();

        $workstation = $query->readUpdatedLoginEntity($loginName, $input['password']);

        $message = Response\Message::create(Render::$request);
        $message->data = $workstation;
        Render::lastModified(time(), '0');
        Render::json($message, Helper\User::getStatus($workstation));
    }
}
