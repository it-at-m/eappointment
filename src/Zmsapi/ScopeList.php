<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Scope as Query;

class ScopeList extends BaseController
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
        $message = Response\Message::create($request);
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $scopeList = (new Query())->readList($resolveReferences);
        if (0 == $scopeList->count()) {
            throw new Exception\Scope\ScopeNotFound(); // @codeCoverageIgnore
        }
        if ((new Helper\User($request))->hasRights()) {
            (new Helper\User($request))->checkRights('scope');
        } else {
            $scopeList = $scopeList->withLessData();
            $message->meta->reducedData = true;
        }

        $message->data = $scopeList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
