<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Organisation as Query;
use \BO\Zmsdb\Scope;

/**
  * Handle requests concerning services
  */
class OrganisationByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scope = (new Scope())->readEntity($itemId, 0);
        if (! $scope->hasId()) {
            error_log(print_r($scope, 1));
            throw new Exception\Scope\ScopeNotFound();
        }
        $organisation = (new Query())->readByScopeId($itemId, $resolveReferences);
        if (! $organisation->hasId()) {
            throw new Exception\Organisation\OrganisationNotFound();
        }
        $message = Response\Message::create(Render::$request);
        $message->data = $organisation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
