<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Mail;

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
        $entity = new \BO\Zmsentities\Process($input);
        $process = (new Query())->updateProcessStatus($entity, 'confirmed');

        //write mail in queue
        $entity = Notification\Mail::getEntityData($process);
        (new Mail())->writeInMailQueue($entity);

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
