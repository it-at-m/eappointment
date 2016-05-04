<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;

/**
  * Handle requests concerning services
  */
class OrganisationGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $message->data = \BO\Zmsentities\Organisation::createExample();
        $message->data->id = $itemId;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
