<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Ticketprinter as Query;

/**
  * Handle requests concerning services
  */
class TicketprinterGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($hash)
    {
        error_log($hash);
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $ticketprinter = $query->readByHash($hash);
        if (! $ticketprinter->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterNotFound();
        }
        $message->data = $ticketprinter;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
