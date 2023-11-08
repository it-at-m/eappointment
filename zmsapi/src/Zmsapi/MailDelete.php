<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Mail as Query;

class MailDelete extends BaseController
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
        $query = new Query();
        $mail = $query->readEntity($args['id']);
        if ($mail && ! $mail->hasId()) {
            throw new Exception\Mail\MailNotFound();
        }

        // @codeCoverageIgnoreStart
        if (! $query->deleteEntity($mail->id)) {
            throw new Exception\Mail\MailDeleteFailed();
        }
        // @codeCoverageIgnoreEnd

        $message = Response\Message::create($request);
        $message->data = $mail;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}
