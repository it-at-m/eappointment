<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Status as Query;

/**
  * Handle requests concerning services
  */
class Healthcheck extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $status = (new Query())->readEntity();
        Render::lastModified(time(), '0');
        echo "OK - DB=" . $status['database']['nodeConnections'] . "%";
    }
}
