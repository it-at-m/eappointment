<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;

class ProcessFree extends BaseController
{
    /**
     *
     * @return String
     */
    public static function render()
    {
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            Helper\User::checkRights();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        $input = Validator::input()->isJson()->assertValid()->getValue();
        $query = new Query();
        $entity = new \BO\Zmsentities\Calendar($input);
        $processList = $query->readFreeProcesses($entity, \App::getNow(), $slotType, $slotsRequired);

        $message = Response\Message::create(Render::$request);
        $message->data = null;
        if (!$processList->getFirstProcess()) {
            throw new Exception\Process\FreeProcessListEmpty();
        } else {
            $message->data = $processList->withLessData();
        }

        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
