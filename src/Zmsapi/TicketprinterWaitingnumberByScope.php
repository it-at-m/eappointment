<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Ticketprinter as Query;
use \BO\Zmsdb\Scope;
use \BO\Zmsdb\Process;

/**
  * Handle requests concerning services
  */
class TicketprinterWaitingnumberByScope extends BaseController
{
    /**
     * @return String
     */
    public static function render($scopeId, $hash)
    {
        $message = Response\Message::create(Render::$request);
        $ticketprinter = (new Query())->readByHash($hash);

        if (! $ticketprinter->hasId()) {
            throw new Exception\Ticketprinter\TicketprinterNotFound();
        }
        if (! $ticketprinter->isEnabled()) {
            throw new Exception\Ticketprinter\TicketprinterNotEnabled();
        }

        $scope = (new Scope())->readEntity($scopeId, 0);
        if (! $scope->hasId()) {
            throw new Exception\Scope\ScopeNotFound();
        }

        $process = (new Process())->writeNewFromTicketprinter($scope->id, \App::$now);
        if (! $process->hasId()) {
            throw new Exception\Process\ProcessFailedReservation();
        }

        $message->data = $process;
        Render::lastModified(time(), '0');
        Render::json($message->setUpdatedMetaData(), $message->getStatuscode());
    }
}
