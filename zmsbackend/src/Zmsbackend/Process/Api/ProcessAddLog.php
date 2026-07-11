<?php

/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsbackend\Process\Api;

use BO\Slim\Render;
use BO\Mellon\Validator;
use BO\Zmsbackend\Log\Service\Log as Query;
use BO\Zmsbackend\Process\Service\Process;
use BO\Zmsentities\Process as ProcessEntity;

class ProcessAddLog extends \BO\Zmsbackend\Api\BaseController
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
        (new \BO\Zmsbackend\Helper\User($request))->checkPermissions('superuser');

        $processId = Validator::value($args['id'])->isNumber()->getValue();

        /** @var ProcessEntity $process */
        $process = (new \BO\Zmsbackend\Process\Service\Process())->readById($processId);
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $mimepart = new \BO\Zmsentities\Mimepart($input);
        $mimepart->testValid();

        $isError = Validator::param('error')->isNumber()->setDefault(0)->getValue();
        if ($isError) {
            Query::writeProcessLog(
                "MTA failed, message=" . $mimepart->content,
                Query::ACTION_MAIL_FAIL,
                $process
            );
        } else {
            Query::writeProcessLog(
                "MTA successful, subject=" . $mimepart->content,
                Query::ACTION_MAIL_SUCCESS,
                $process
            );
        }

        $message = \BO\Zmsbackend\Api\Response\Message::create($request);
        $message->data = $mimepart;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
