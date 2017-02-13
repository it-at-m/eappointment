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
class OrganisationGet extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $organisation = (new Query())->readEntity($itemId, $resolveReferences);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }
        if (Helper\User::hasRights()) {
            Helper\User::checkRights('organisation');
        } else {
            $organisation = $organisation->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $organisation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
