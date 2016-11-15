<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Ticketprinter as Ticketprinter;
use \BO\Zmsdb\Organisation as Query;

/**
  * Handle requests concerning services
  */
class OrganisationHash extends BaseController
{
    /**
     * @return String
     */
    public static function render($organisationId)
    {
        $message = Response\Message::create(Render::$request);
        $organisation = (new Query())->readEntity($organisationId);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }
        $ticketprinter = (new Ticketprinter())->writeEntityWithHash($organisationId);
        $message->data = $ticketprinter;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
