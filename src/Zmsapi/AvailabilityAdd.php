<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

/**
  * Handle requests concerning services
  */
class AvailabilityAdd extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Availability($input);
        $message->data = (new Query())->writeEntity($entity);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
