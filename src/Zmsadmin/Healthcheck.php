<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use BO\Slim\Render;

/**
  * Handle requests concerning services
  *
  */
class Healthcheck extends BaseController
{
    /**
     * @return String
     */
    public function render()
    {
        $result = \App::$http->readGetResult('/status/');
        $status = $result->getEntity();
        Render::lastModified(time(), '0');
        echo "OK - DB=" . $status['database']['nodeConnections'] . "%";
    }
}
