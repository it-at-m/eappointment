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
        $ids = Validator::param('ids')->isString()->setDefault('')->getValue();
        $query = new Query();
        
        if ($onlyIds) {
            if (!empty($ids)) {
                $itemIds = array_map('intval', explode(',', $ids));
                $result = $query->readEntitiesIds($itemIds, $resolveReferences,  $limit, 'ASC');
            } else {
                $result = $query->readListIds($resolveReferences, $limit, 'ASC');
            }
        } else {
            if (!empty($ids)) {
                $itemIds = array_map('intval', explode(',', $ids));
                $result = $query->readEntities($itemIds, $resolveReferences, $limit, 'ASC');
            } else {
                $result = $query->readList($resolveReferences, $limit, 'ASC');
            }
        }

        $message = Response\Message::create($request);
        $message->data = $result;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
