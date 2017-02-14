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

        Helper\User::checkRights('scope');
        $query = new Query();
        $scope = $query->readEntity($itemId)->withLessData();
        if (! $scope) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $input = Validator::input()->isJson()->getValue();
        $entity = new \BO\Zmsentities\MailPart($input);

        $message->data = $query->writeImageData($itemId, $entity);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
