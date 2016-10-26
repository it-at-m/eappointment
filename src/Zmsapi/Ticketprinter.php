<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsentities\Ticketprinter as Entity;

/**
  * Handle requests concerning services
  */
class Ticketprinter extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $entity = new Entity($input);
        $entity->testValid();
        /* check if necessary
        $reference = $query->readByHash($entity->hash);
        if (! $reference->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterHashNotValid();
        }
        */
        if (! $entity->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterNotFound();
        }

        $ticketprinter = $query->readByButtonList($entity, \App::$now);
        $message->data = $ticketprinter;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
