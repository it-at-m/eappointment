<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;

/**
  * Handle requests concerning services
  */
class ProcessReservedList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $message = Response\Message::create(Render::$request);
        $message->data = $query->readReservedProcesses($resolveReferences);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
