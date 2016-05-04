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
  */
class ProviderList extends BaseController
{
    /**
     * @return String
     */
    public static function render($source, $requestIds = null)
    {
        $providerList = (new Query())->readProviderByRequest($source, $requestIds);
        $message = Response\Message::create(Render::$request);
        $message->data = $providerList;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
