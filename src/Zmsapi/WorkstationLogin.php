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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();

        if ($query->isUserExisting($loginName, $input['password'])) {
            $status = 200;
            $workstation = $query->readUpdatedLoginEntity($loginName, $input['password'], $resolveReferences);
            $message->data = $workstation;
        } else {
            $status = 404;
            $message->data = null;
        }

        Render::lastModified(time(), '0');
        Render::json($message, $status);
    }
}
