<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Organisation as Query;

class OrganisationGet extends BaseController
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
        $workstation = new Helper\User($request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $organisation = (new Query())->readEntity($args['id'], $resolveReferences);
        if (! $organisation) {
            throw new Exception\Organisation\OrganisationNotFound();
        }

        $message = Response\Message::create($request);

        if ($workstation->hasRights()) {
            $workstation->checkRights('department');
        } else {
            $organisation = $organisation->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $organisation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
