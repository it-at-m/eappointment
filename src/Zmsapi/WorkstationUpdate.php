<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;

/**
  * Handle requests concerning services
  */
class WorkstationUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($loginName)
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Workstation($input);
        $message->data = (new Query)->updateEntity($loginName, $entity);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
