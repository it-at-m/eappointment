<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;
use \BO\Zmsdb\Workstation as WorkstationQuery;
use \BO\Zmsentities\Scope;

/**
  * Handle requests concerning services
  */
class ScopeEmergencyRespond extends BaseController
{
    /**
     * @return String
     */
    public static function render($itemId)
    {
        $workstation = Helper\User::checkRights();
        if (!$workstation->scope instanceof Scope || $workstation->scope->id != $itemId) {
            throw new Exception\Scope\ScopeNoAccess();
        }
        $message = Response\Message::create(Render::$request);
        $workstation->scope->status['emergency']['acceptedByWorkstation'] =
            $workstation->name ? $workstation->name : "Tresen";
        $message->data = (new Query)->updateEmergency($itemId, $workstation->scope);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
