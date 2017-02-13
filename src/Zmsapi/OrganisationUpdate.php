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
class OrganisationUpdate extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $message = Response\Message::create(Render::$request);
        $input = Validator::input()->isJson()->getValue();
        $organisation = new \BO\Zmsentities\Organisation($input);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }
        if (Helper\User::hasRights()) {
            Helper\User::checkRights('organisation');
            $organisation = (new Query())->updateEntity($itemId, $organisation);
        } else {
            $organisation = $organisation->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $organisation;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
