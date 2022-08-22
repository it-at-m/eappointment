<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsadmin\Helper\LoginForm;
use \BO\Mellon\Validator;
use Jumbojett\OpenIDConnectClient;

class Index extends BaseController
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
        try {
            $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
        } catch (\Exception $workstationexception) {
            $workstation = null;
        }
        $input = $request->getParsedBody();
        
        if(\App::AUTHORIZATION_TYPE === "OPENID"){ //TODO: "OPENID" as Constante
            //TODO: create new class/Method and move functionality to it?
            $oidc = new OpenIDConnectClient(\App::AUTHORIZATION_PROVIDER_URL,
                                            \App::AUTHORIZATION_CLIENT_ID,
                                            \App::AUTHORIZATION_CLIENT_SECRET);

            $oidc->setVerifyPeer(false); //TODO: check for development enviroment

            try {
                $oidc->authenticate();
                $accessTokenPayload = $oidc->getAccessTokenPayload();
                
                //TODO: check if user exists 

            
                //TODO: if false create user
                $userAccount = new \BO\Zmsentities\Useraccount(array(
                    'id' => "superuser",
                    'password' => "vorschau",
                    'departments' => array('id' => 0) // required in schema validation
                ));
                
                $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
        
         
                $newUser = array(
                    "lastLogin" => 1447926465,
                    "id" => $accessTokenPayload->preferred_username,
                    "email" => $accessTokenPayload->email,
                    "password" => $accessTokenPayload->sid,
                    "rights" => array(
                        "scope" => true,
                        "ticketprinter" => true
                    ),
                    "departments" => array(
                        "id" => 72,
                    )
                );
                error_log("_____TEST___________________________________: " . json_encode($accessTokenPayload));
                $entity = new Entity($newUser);
                $entity = $entity->withCleanedUpFormData(true);
                //TODO: try catch (superuser stays logged in after error)
                $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
                
                //Logout superuser
                \App::$http->readDeleteResult('/workstation/login/'. $workstation->useraccount['id'] .'/')->getEntity();
                
                //TODO: login with user
                $workstation = \App::$http->readPostResult('/workstation/login/', $entity)->getEntity();
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
            } catch (\Jumbojett\OpenIDConnectClientException $e) {
                //throw $exception;
                echo $e; //TODO: Logging
            }
            return \BO\Slim\Render::redirect('workstationSelect', array(), array());
        }
        
        if (is_array($input) && array_key_exists('loginName', $input)) {
            return $this->testLogin($input, $response);
        }
        $config = (! $workstation)
            ? \App::$http->readGetResult('/config/', [], \App::CONFIG_SECURE_TOKEN)->getEntity()
            : null;
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung',
                'config' => $config,
                'workstation' => $workstation
            )
        );
    }

    protected function testLogin($input, $response)
    {
        $userAccount = new \BO\Zmsentities\Useraccount(array(
            'id' => $input['loginName'],
            'password' => $input['password'],
            'departments' => array('id' => 0) // required in schema validation
        ));
        try {
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            if (array_key_exists('authkey', $workstation)) {
                \BO\Zmsclient\Auth::setKey($workstation->authkey);
                return \BO\Slim\Render::redirect('workstationSelect', array(), array());
            }
        } catch (\BO\Zmsclient\Exception $exception) {
            $template = Helper\TwigExceptionHandler::getExceptionTemplate($exception);
            if ('BO\Zmsentities\Exception\SchemaValidation' == $exception->template) {
                $exceptionData = [
                  'template' => 'exception/bo/zmsapi/exception/useraccount/invalidcredentials.twig'
                ];
                $exceptionData['data']['password']['messages'] = [
                    'Der Nutzername oder das Passwort wurden falsch eingegeben'
                ];
            } elseif ('BO\Zmsapi\Exception\Useraccount\UserAlreadyLoggedIn' == $exception->template) {
                \BO\Zmsclient\Auth::setKey($exception->data['authkey']);
                throw $exception;
            } elseif ('' != $exception->template
                && \App::$slim->getContainer()->view->getLoader()->exists($template)
            ) {
                $exceptionData = [
                  'template' => $template,
                  'data' => $exception->data
                ];
            } else {
                throw $exception;
            }
        }
        return \BO\Slim\Render::withHtml(
            $response,
            'page/index.twig',
            array(
                'title' => 'Anmeldung gescheitert',
                'loginfailed' => true,
                'workstation' => null,
                'exception' => $exceptionData
            )
        );
    }
}
