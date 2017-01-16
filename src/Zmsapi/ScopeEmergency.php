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
class ScopeEmergency extends BaseController
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
        $workstation->scope->status['emergency']['activated'] = 1;
        $workstation->scope->status['emergency']['calledByWorkstation'] =
            $workstation->name ? $workstation->name : "Tresen";
        $workstation->scope->status['emergency']['acceptedByWorkstation'] = -1;
        $message->data = (new Query)->updateEmergency($itemId, $workstation->scope);
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
