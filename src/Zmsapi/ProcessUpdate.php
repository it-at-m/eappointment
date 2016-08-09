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
class ProcessUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId, $authKey)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        $process->id = $itemId;
        $process->authKey = $authKey;
        $message->data = $query->updateEntity($process);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
