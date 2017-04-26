<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Notification as Query;
use \BO\Mellon\Validator;

/**
  * Handle requests concerning services
  */
class NotificationAdd extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        Helper\User::checkRights('sms');

        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Notification($input);
        $queueId = (new Query())->writeInQueue($entity);
        $message->data = $entity;
        $message->data->id = $queueId;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
