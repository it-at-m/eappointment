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
class ProcessGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $process = (new Query())->readEntity($itemId, $authKey, $resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
