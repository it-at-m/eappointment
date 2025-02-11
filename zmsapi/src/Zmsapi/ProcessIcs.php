<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use BO\Slim\Render;
use BO\Zmsdb\Process;
use BO\Zmsdb\Config;
use BO\Mellon\Validator;

class ProcessIcs extends BaseController
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
        $status = Validator::param('status')->isNumber()->setDefault('appointment')->getValue();
        $this->testProcessData($args['id'], $args['authKey']);
        $process = (new Process())->readEntity($args['id'], $args['authKey'], 2);

        $config = (new Config())->readEntity();
        $templateProvider = new \BO\Zmsdb\Helper\MailTemplateProvider($process);
        $ics = \BO\Zmsentities\Helper\Messaging::getMailIcs($process, $config, $status, null, null, $templateProvider);

        $message = Response\Message::create($request);
        $message->data = $ics;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }

    protected function testProcessData($processId, $authKey)
    {
        $authCheck = (new Process())->readAuthKeyByProcessId($processId);
        if (! $authCheck) {
            throw new Exception\Process\ProcessNotFound();
        } elseif ($authCheck['authKey'] != $authKey && $authCheck['authName'] != $authKey) {
            throw new Exception\Process\AuthKeyMatchFailed();
        }
    }
}
