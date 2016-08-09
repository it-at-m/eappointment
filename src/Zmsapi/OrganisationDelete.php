<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Organisation as Query;

/**
  * Handle requests concerning services
  */
class OrganisationDelete extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $entity = $query->readEntity($itemId);
        $query->deleteEntity($itemId);
        $message->data = $entity;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
