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

        if (! $entity->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterNotFound();
        }
        if (! $entity->isEnabled()) {
            throw new Exception\Ticketprinter\TicketprinterNotEnabled();
        }

        $message->data = $query->readByButtonList($entity, \App::$now);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
