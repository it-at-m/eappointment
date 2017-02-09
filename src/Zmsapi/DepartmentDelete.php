<?php

/**
 *
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Department as Query;

/**
 * Delete a department
 */
class DepartmentDelete extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render($itemId)
    {
        $query = new Query();
        $message = Response\Message::create(Render::$request);
        $message->data = $query->deleteEntity($itemId);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
