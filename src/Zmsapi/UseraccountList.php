<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Useraccount;

class UseraccountList extends BaseController
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
        $validator = $request->getAttribute('validator');
        (new Helper\User($request))->checkRights('useraccount');
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $rightRestriction = $validator->getParameter('right')->isString()->getValue();

        $useraccountList = (new Useraccount)->readList($resolveReferences);
        if ($rightRestriction) {
            $useraccountList = $useraccountList->withRights([$rightRestriction]);
        }
        $message = Response\Message::create($request);
        $message->data = $useraccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), 200);
        return $response;
    }
}
