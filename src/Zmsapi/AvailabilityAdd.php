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
        $collection = new \BO\Zmsentities\Collection\AvailabilityList();
        foreach ($input as $availability) {
            $entity = new \BO\Zmsentities\Availability($availability);
            if ($entity->id) {
                $entity = (new Query())->updateEntity($entity->id, $entity);
            } else {
                $entity = (new Query())->writeEntity($entity);
            }
            $collection[] = $entity;
        }
        $message->data = $collection->getArrayCopy();
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
