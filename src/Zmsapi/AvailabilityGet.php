<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Availability as Query;

/**
 * Handle requests concerning services
 */
class AvailabilityGet extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $availability = (new Query())->readEntity($itemId, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $availability;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
