<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use \BO\Zmsadmin\Helper\LoginForm;
use \BO\Mellon\Validator;

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
        
        error_log("____________________________________OOOOOOO___________________________________: " .  $request->getParam("code")  );
        $input = $request->getParsedBody();
        if ((is_array($input) && array_key_exists('loginName', $input)) ||
            (\App::ZMS_AUTHORIZATION_TYPE === "Keycloak")
        ) {
            return $this->testLogin($input, $response, $request);
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

    protected function testLogin($input, $response, $request)
    {
        if(\App::ZMS_AUTHORIZATION_TYPE === "Keycloak"){ 
            try {
                $oAuth = new OAuth($request);
                $oAuth->Authorization();
            } catch (\Exception $workstationexception) {
                error_log("____________________________________222222___________________________________: ". $workstationexception);
            }
            return \BO\Slim\Render::redirect('workstationSelect', array(), array());
        }
        
        try {
            $userAccount = new \BO\Zmsentities\Useraccount(array(
                'id' => $input['loginName'],
                'password' => $input['password'],
                'departments' => array('id' => 0) // required in schema validation
            ));
            
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
