<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Owner as Query;

/**
 * Delete an owner by Id
 */
class OwnerDelete extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $entity = $query->readEntity($itemId, 1);
        if ($entity->toProperty()
            ->offsetExists('departments')) {
            throw new Exception\Owner\OrganisationListNotEmpty();
        } else {
            $query->deleteEntity($itemId);
        }
        $message->data = $entity;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
