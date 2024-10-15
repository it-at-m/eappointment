<?php
/**
 * @package ZMS API
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

        $ids = $request->getQueryParams()['ids'] ?? null;

        if ($ids) {
            $itemIds = array_map('intval', explode(',', $ids));
            if (empty($itemIds)) {
                throw new \InvalidArgumentException('No valid IDs provided for deletion.');
            }

            if (!$query->deleteEntities($itemIds)) {
                throw new Exception\Mail\MailDeleteFailed();
            }

            $message = Response\Message::create($request);
            $message->data = ['deleted' => $itemIds];
            $response = Render::withLastModified($response, time(), '0');
            $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
            return $response;
        }

        if (!isset($args['id'])) {
            throw new \InvalidArgumentException('No valid ID provided for deletion.');
        }

        $mail = $query->readEntity($args['id']);
        if ($mail && !$mail->hasId()) {
            throw new Exception\Mail\MailNotFound();
        }

        if (!$query->deleteEntity($mail->id)) {
            throw new Exception\Mail\MailDeleteFailed();
        }

        $message = Response\Message::create($request);
        $message->data = $mail;
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}
