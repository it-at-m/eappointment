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
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $query = new Query();

        if (Helper\User::hasRights()) {
            Helper\User::checkRights('basic');
            $message->data = $query->readEntity($itemId, $resolveReferences);
        } else {
            $message->data = $query->readEntity($itemId, $resolveReferences)->withLessData();
            $message->meta->reducedData = true;
        }

        Render::lastModified(time(), '0');
        Render::json($message, $message->getStatuscode());
    }
}
