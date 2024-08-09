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
        
        // Extract parameters from the request
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(2)->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(300)->getValue();
        $onlyIds = Validator::param('onlyIds')->isBool()->setDefault(false)->getValue();
        $ids = Validator::param('ids')->isString()->setDefault('')->getValue();

        // Initialize the query object
        $query = new Query();

        // Check if 'ids' parameter is present
        if (!empty($ids)) {
            // Convert the comma-separated list into an array of integers
            $itemIds = array_map('intval', explode(',', $ids));
            // Use readEntities method to fetch specific mails by IDs
            $mailList = $query->readEntities($itemIds, $resolveReferences, $onlyIds);
        } else {
            // Use readList method to fetch mails with default behavior
            $mailList = $query->readList($resolveReferences, $limit, 'ASC', $onlyIds);
        }

        // Create the response message
        $message = Response\Message::create($request);
        $message->data = $mailList;

        // Return the response as JSON
        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);
        return $response;
    }
}
