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
class ScopeGet extends BaseController
{

    /**
     *
     * @return String
     */
    public static function render($itemId)
    {
        Helper\User::checkRights('basic');

        $query = new Query();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $scope = $query->readEntity($itemId, $resolveReferences);

        $message = Response\Message::create(Render::$request);
        $message->data = $scope;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
