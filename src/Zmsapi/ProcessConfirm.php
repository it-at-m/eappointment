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
class ProcessConfirm extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $query = new Query();
        $process = new \BO\Zmsentities\Process($input);
        $message->data = $query->updateProcessStatus($process, 'confirmed');
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
