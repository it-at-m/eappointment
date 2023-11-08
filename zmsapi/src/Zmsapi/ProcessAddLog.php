<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Log as Query;

class ProcessAddLog extends BaseController
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
        (new Helper\User($request))->checkRights('superuser');
        $processId = Validator::value($args['id'])->isNumber()->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $mimepart = new \BO\Zmsentities\Mimepart($input);
        $mimepart->testValid();

        $isError = Validator::param('error')->isNumber()->setDefault(0)->getValue();
        if ($isError) {
            Query::writeLogEntry("MTA failed, message=". $mimepart->content, $processId);
        } else {
            Query::writeLogEntry("MTA successful, subject=". $mimepart->content, $processId);
        }

        $message = Response\Message::create($request);
        $message->data = $mimepart;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
