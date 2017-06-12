<?php
/**
 * @package ZMS API
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\Useraccount;

class WorkstationLogin extends BaseController
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
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $useraccount = new \BO\Zmsentities\Useraccount($input);
        $useraccount->testValid();

        if (! (new Useraccount)->readIsUserExisting($useraccount->id) || 0 == count($input)) {
            throw new Exception\Useraccount\UseraccountNotFound();
        }

        $logInHash = (new Workstation)->readLoggedInHashByName($useraccount->id);
        $workstation = (new Workstation)->writeEntityLoginByName(
            $useraccount->id,
            $useraccount->password,
            \App::getNow(),
            $resolveReferences
        );

        if (null !== $logInHash) {
            //to avoid commit on unit tests, is there a better solution?
            $noCommit = $validator->getParameter('nocommit')->isNumber()->setDefault(0)->getValue();
            if (!$noCommit) {
                \BO\Zmsdb\Connection\Select::writeCommit();
            }
            $exception = new \BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn();
            $exception->data = $workstation;
            throw $exception;
        }

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        return $response;
    }
}
