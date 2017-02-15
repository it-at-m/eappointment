<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

/**
  * Handle requests concerning services
  */
class ScopeCalldisplayImageDataUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $query = new Query();
        $scope = $query->readEntity($itemId);
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        Helper\User::checkRights('scope');
        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\Mimepart($input);

        $message->data = $query->writeImageData($itemId, $entity);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
