<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Provider as Query;

/**
  * Handle requests concerning services
  *
  */
class ProviderList extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $itemIds = null)
    {
        $providers = (new Query())->readList($source, $itemIds);
        $message = Response\Message::create();
        $message->data = $providers;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
