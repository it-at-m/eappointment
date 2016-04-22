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
        $entity = new \BO\Zmsentities\Process($input);
        $process = (new Query())->updateProcessStatus($entity, 'confirmed');

        //write mail in queue
        $mail = Notification\Mail::getEntityData($process);
        $mail = new MailAdd($mail);

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
