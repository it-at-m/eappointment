<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\ProcessStatusFree;

class ProcessReserve extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        if ($process->hasId()) {
            throw new Exception\Process\ProcessReserveFailed();
        }

        \BO\Zmsdb\Connection\Select::getWriteConnection();
        if ($slotType || $slotsRequired) {
            (new Helper\User($request))->checkRights();
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
            $process = (new Process)->readSlotCount($process);
        }

        $process = ProcessStatusFree::init()->writeEntityReserved($process, \App::$now, $slotType, $slotsRequired);

        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
