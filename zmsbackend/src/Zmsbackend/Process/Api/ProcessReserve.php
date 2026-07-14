<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Process\Service\ProcessStatusFree;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessReserve extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @SuppressWarnings(Complexity)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
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
            throw new \BO\Zmsbackend\Process\Exception\ProcessAlreadyExists();
        }

        \BO\Zmsbackend\Connection\Select::setCriticalReadSession();

        if ($slotType || $slotsRequired) {
            $workstation = (new \BO\Zmsbackend\Helper\User($request))->checkPermissions();
            \BO\Zmsbackend\Helper\Matching::testCurrentScopeHasRequest($process);
        } elseif ($clientKey) {
            $apiClient = (new \BO\Zmsbackend\Apikey\Service\Apiclient())->readEntity($clientKey);
            if (!$apiClient || !isset($apiClient->accesslevel) || $apiClient->accesslevel == 'blocked') {
                throw new \BO\Zmsbackend\Process\Exception\ApiclientInvalid();
            }
            $slotType = $apiClient->accesslevel;
            if ($apiClient->accesslevel != 'intern') {
                $slotsRequired = 0;
                $slotType = $apiClient->accesslevel;
                $process = (new \BO\Zmsbackend\Process\Service\Process())->readSlotCount($process);
            }
            $process->apiclient = $apiClient;
        } else {
            $slotsRequired = 0;
            $slotType = 'public';
            $process = (new \BO\Zmsbackend\Process\Service\Process())->readSlotCount($process);
        }

        $userAccount = (isset($workstation)) ? $workstation->getUseraccount() : null;
        $process = (new \BO\Zmsbackend\Process\Service\ProcessStatusFree())
            ->writeEntityReserved($process, \App::$now, $slotType, $slotsRequired, $resolveReferences, $userAccount);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $process;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
