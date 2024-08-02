<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Mail as Query;

class MailList extends BaseController
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
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(300)->getValue();
        $onlyIds = Validator::param('onlyIds')->isBool()->setDefault(false)->getValue();
        
        $mailList = (new Query())->readList($resolveReferences, $limit, 'ASC', $onlyIds);

        if ($onlyIds) {
            $mailList = array_map(function ($mail) {
                return $mail->id;
            }, $mailList);
        }

        $message = Response\Message::create($request);
        $message->data = $mailList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
