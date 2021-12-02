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

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessReserve extends BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @SuppressWarnings(Complexity)
     * @return String
     */
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $slotsRequired = Validator::param('slotsRequired')->isNumber()->getValue();
        $slotType = Validator::param('slotType')->isString()->getValue();
        $clientKey = Validator::param('clientkey')->isString()->getValue();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $process = new \BO\Zmsentities\Process($input);
        if ($process->hasId()) {
            throw new Exception\Process\ProcessAlreadyExists();
        }

        \BO\Zmsdb\Connection\Select::setCriticalReadSession();
        
        if ($slotType || $slotsRequired) {
            $workstation = (new Helper\User($request))->checkRights();
            Helper\Matching::testCurrentScopeHasRequest($process);
        } elseif ($clientKey) {
            $apiClient = (new \BO\Zmsdb\Apiclient)->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new Exception\Process\ApiclientInvalid();
            }
            $slotType = $apiClient->accesslevel;
            if ($apiClient->accesslevel != 'intern') {
                $slotsRequired = 0;
                $slotType = $apiClient->accesslevel;
                $process = (new Process)->readSlotCount($process);
            }
            $process->apiclient = $apiClient;
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
            $process = (new Process)->readSlotCount($process);
        }

        $userAccount = (isset($workstation)) ? $workstation->getUseraccount() : null;
        $process = (new ProcessStatusFree)
            ->writeEntityReserved($process, \App::$now, $slotType, $slotsRequired, $resolveReferences, $userAccount);
        $message = Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
