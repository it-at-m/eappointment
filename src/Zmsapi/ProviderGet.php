<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Provider as Query;

/**
  * Handle requests concerning services
  */
class ProviderGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $itemId)
    {
        $provider = (new Query())->readEntity($source, $itemId);
        $message = Response\Message::create();
        $message->data = $provider;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
