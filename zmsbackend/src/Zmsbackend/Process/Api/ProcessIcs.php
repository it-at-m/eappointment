<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsbackend\Config\Service\Config;
use BO\Mellon\Validator;

class ProcessIcs extends \BO\Zmsbackend\Api\BaseController
{
    /**
     * @SuppressWarnings(Param)
     * @return \Psr\Http\Message\ResponseInterface
     */
    #[\Override]
    public function readResponse(
        \Psr\Http\Message\RequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response,
        array $args
    ) {
        $status = Validator::param('status')->isNumber()->setDefault('appointment')->getValue();
        $this->testProcessData($args['id'], $args['authKey']);
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readEntity($args['id'], $args['authKey'], 2);

        $config = (new \BO\Zmsbackend\Config\Service\Config())->readEntity();
        $templateProvider = new \BO\Zmsbackend\Helper\MailTemplateProvider($process);
        $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config, $status, null, null, $templateProvider);

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $ics;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($processId, $authKey)
    {
        $authCheck = (new \BO\Zmsbackend\Process\Service\Process())->readAuthKeyByProcessId($processId);
        if (! $authCheck) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessNotFound();
        } elseif ($authCheck['authKey'] !== $authKey) {
            throw new \BO\Zmsbackend\Process\Exception\AuthKeyMatchFailed();
        }
    }
}
