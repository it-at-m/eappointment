<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  *
  */
class AvailabilityList extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId)
    {
        $message = Response\Message::create();
        $scopeId = $scopeId; // @todo fetch data
        $message->data = array(\BO\Zmsentities\Availability::createExample());
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
