<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Process;
use \BO\Zmsdb\Useraccount;

class UseraccountSearch extends BaseController
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
        $workstation = (new Helper\User($request, 2))->checkRights();
        $resolveReferences = Validator::param('resolveReferences')->isNumber()->setDefault(0)->getValue();
        $lessResolvedData = Validator::param('lessResolvedData')->isNumber()->setDefault(0)->getValue();
        $limit = Validator::param('limit')->isNumber()->setDefault(100)->getValue();

        $parameters = $request->getParams();
        unset($parameters['resolveReferences']);
        unset($parameters['lessResolvedData']);
        unset($parameters['limit']);
        $userAccountList = (new Useraccount)->readSearch($parameters, $resolveReferences, $limit);

        if ($lessResolvedData) {
            $userAccountList = $userAccountList->withLessData();
        }   
       
        $message = Response\Message::create($request);
        $message->data = $userAccountList;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message, 200);

        return $response;
    }
}
