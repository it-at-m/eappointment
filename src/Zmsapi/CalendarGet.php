<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Calendar as Query;

/**
  * Handle requests concerning services
  */
class CalendarGet extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $query = new Query();

        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        if (null === $input) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
        } else {
            $entity = new \BO\Zmsentities\Calendar($input);
        }

        if (false === Helper\Matching::isProviderExisting('dldb', $entity['providers'])) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Ein ausgewählter Dienstleister exisistiert nicht";
            $message->meta->exception = "BO/Zmsapi/Helper/Matching";
        } elseif (false === Helper\Matching::isRequestExisting('dldb', $entity['requests'], $entity['providers'])) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Eine ausgewählte Dienstleistung existiert nicht";
            $message->meta->exception = "BO/Zmsapi/Helper/Matching";
        } elseif (false === Helper\Matching::hasProviderRequest('dldb', $entity['requests'], $entity['providers'])) {
            $message->meta->statuscode = 500;
            $message->meta->error = true;
            $message->meta->message = "Die Diensleistung wird nicht vom Dienstleister angeboten";
            $message->meta->exception = "BO/Zmsapi/Helper/Matching";
        } else {
            $message->data = $query->readResolvedEntity($entity, \App::getNow());
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
