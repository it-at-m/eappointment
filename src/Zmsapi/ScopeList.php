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
class ScopeList extends BaseController
{
    /**
     * @return String
     */
    public static function render()
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = (new Query())->readList($resolveReferences);
        $message = Response\Message::create(Render::$request);
        $message->data = $scope;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
