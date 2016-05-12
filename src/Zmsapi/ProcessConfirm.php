<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\Config as Config;

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
        $query = new Query();
        $config = (new Config())->readEntity();
        $message = Response\Message::create();
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Process($input);
        $process = $query->updateProcessStatus($entity, 'confirmed');

        //write mail in queue
        $mail = (new \BO\Zmsentities\Mail())->toResolvedEntity($process, $config);
        MailAdd::render($mail);

        //write notification in queue
        $notification = (new \BO\Zmsentities\Notification())->toResolvedEntity($process, $config);
        NotificationAdd::render($notification);

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
