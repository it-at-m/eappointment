<?php
/**
 * @package Zmsapi
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process as Query;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
  * Handle requests concerning services
  */
class ProcessReserve extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function __invoke(RequestInterface $request, ResponseInterface $response, array $args)
    {
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        if ($slotType || $slotsRequired) {
            (new Helper\User($request))->checkRights();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
        }

        $message = Response\Message::create($request);
        $input = Validator::input()->isJson()->assertValid()->getValue();

        $process = new \BO\Zmsentities\Process($input);
        $query = new Query();

        if ($process->hasId()) {
            throw new Exception\Process\ProcessReserveFailed();
        }
        $process = $query->readSlotCount($process);
        $process = $query->writeEntityReserved($process, \App::$now, $slotType, $slotsRequired);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
