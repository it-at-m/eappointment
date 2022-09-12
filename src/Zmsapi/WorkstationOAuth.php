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
use \BO\Slim\Profiler as Profiler;

/**
 * @SuppressWarnings(Coupling)
 */
class WorkstationOAuth extends BaseController
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
        Profiler::add("____________________START WorkstationOAuth___________________");
        $validator = $request->getAttribute('validator');
        error_log("____________________________________PPPPPPPP___________________________________: " .  $request->getParam("code")  );
        $resolveReferences = $validator->getParameter('code')->isString()->isSmallerThan(10000)->isBiggerThan(1); //TODO: isBase64()
        //$resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $input = Validator::input()->isJson()->assertValid()->getValue();
        $entity = new \BO\Zmsentities\Useraccount($input);
        $entity->testValid();

        \BO\Zmsdb\Connection\Select::getWriteConnection();
        Helper\UserAuth::testUseraccountExists($entity->getId());
        
        $useraccount = Helper\UserAuth::getVerifiedUseraccount($entity);
        //Helper\UserAuth::testPasswordMatching($useraccount, $entity->password);
        
        
        $workstation = (new Helper\User($request, $resolveReferences))->readWorkstation();
        Helper\User::testWorkstationIsOveraged($workstation);

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
                \BO\Zmsdb\Connection\Select::writeCommit(); // @codeCoverageIgnore
            }
            $exception = new \BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn();
            $exception->data = $workstation;
            throw $exception;
        }

        $message = Response\Message::create($request);
        $message->data = $workstation;

        $response = Render::withLastModified($response, time(), '0');
        $response = Render::withJson($response, $message->setUpdatedMetaData(), $message->getStatuscode());
        Profiler::add("_____END WorkstationOAuth_____");
        return $response;
    }
}
