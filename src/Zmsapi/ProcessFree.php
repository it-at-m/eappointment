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
class ProcessFree extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $query = new Query();
        $entity = new \BO\Zmsentities\Calendar($input);
        $processList = $query->readFreeProcesses($entity, \App::getNow());

        $message->data = null;
        if (!$processList->getFirstProcess()) {
            $message->meta->error = true;
            $message->meta->message = "Um einen Termin zu vereinbaren, muss vorher ein Tag ausgewählt werden.";
            $message->meta->exception = "BO/Zmsapi/Exception/FreeProcessListEmpty";
        } else {
            $message->data = $processList;
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
