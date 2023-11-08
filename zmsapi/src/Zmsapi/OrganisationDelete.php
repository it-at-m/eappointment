<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 */

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Zmsdb\Organisation as Query;

/**
 * Delete an organisation by Id
 */
class OrganisationDelete extends BaseController
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
        $query = new Query();
        $organisation = $query->readEntity($args['id'], 1);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }(new Helper\User($request, 2))->checkRights(
            'organisation',
            new \BO\Zmsentities\Useraccount\EntityAccess($organisation)
        );
        $query->deleteEntity($organisation->id);

        $message = Response\Message::create($request);
        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
