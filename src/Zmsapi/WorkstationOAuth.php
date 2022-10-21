<?php

namespace BO\Zmsapi;

use \BO\Slim\Render;
use \BO\Mellon\Validator;
use \BO\Zmsdb\Workstation;
use \BO\Zmsdb\Useraccount;

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
        $validator = $request->getAttribute('validator');
        $resolveReferences = $validator->getParameter('resolveReferences')->isNumber()->setDefault(1)->getValue();
        $oAuthCode  = $validator->getParameter('code')->isString()->isSmallerThan(120)->isBiggerThan(100);
        $userData = Validator::input()->isJson()->getValue();
        $useraccount = $this->getUseraccount($userData);

        $workstation = (new Helper\User($request, $resolveReferences))->readWorkstation();
        Helper\User::testWorkstationIsOveraged($workstation);

        $logInHash = (new Workstation)->readLoggedInHashByName($useraccount->id);
        $workstation = (new Workstation)->writeEntityLoginByName(
            $useraccount->id,
            $useraccount->password,
            \App::getNow()
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
        return $response;
    }

    private function logoutSuperuser($superuserAccountId){
        (new Workstation)->writeEntityLogoutByName($superuserAccountId);
    }

    private function loginSuperuser(){
        $superuserAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => \App::ZMS_AUTHORIZATION_SUPERUSER_USERNAME,
            'password' => \App::ZMS_AUTHORIZATION_SUPERUSER_PASSWORD,
            'departments' => array('id' => 0) // required in schema validation
        ));

        $superuserAccount = Helper\UserAuth::getVerifiedUseraccount($superuserAccount);
        (new Workstation)->writeEntityLoginByName(
            $superuserAccount->id,
            $superuserAccount->password,
            \App::getNow()
        );

        return $superuserAccount->id;
    }

    private function getUseraccount($userData){
        $userAccount = array(
            "id" => $userData['preferred_username'],
            "email" => $userData['email'],
            "departments" => array(
                "id" => 0,
            )
        );

        if (!(new Useraccount)->readIsUserExisting($userAccount->id)) {
            $superuserAccountId = $this->loginSuperuser();
            $userAccount = $this->addUseraccount($userAccount);
            $this->logoutSuperuser($superuserAccountId);
        }

        return Helper\UserAuth::getVerifiedUseraccount($userAccount);
    }

    private function addUseraccount($user){
        $entity = new \BO\Zmsentities\Useraccount($user);
        $entity->password = $entity->getHash($entity->password);
        return (new Useraccount)->writeEntity($entity);
    }
}
