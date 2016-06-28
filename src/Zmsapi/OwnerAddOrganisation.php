<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Organisation as Query;

/**
  * Handle requests concerning services
  */
class OwnerAddOrganisation extends BaseController
{
    /**
     * @return String
     */
    public static function render($parentId)
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Organisation($input);
        $message->data = (new Query())->writeEntity($entity, $parentId);
        Render::lastModified(time(), '0');
        Render::json($message);
    }
}
