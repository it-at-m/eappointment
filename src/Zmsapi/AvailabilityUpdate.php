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
class AvailabilityUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Availability($input);
        $message->data = (new Query())->updateEntity($itemId, $entity);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
