<?php
/**
 * @package Zmsadmin
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsadmin;

use Stevenmaguire\OAuth2\Client\Provider\Keycloak;
use GuzzleHttp\Client;
use \BO\Zmsentities\Cluster as Entity;
use \BO\Slim\Profiler as Profiler;

class OAuth
{
    private $provider = null;
    private $request = null;
    private $accessTokenPayload = "";

    public function __construct($request)
    {
        $guzzyClient = new Client([
            'defaults' => [
                \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => \App::ZMS_AUTHORIZATION_CONNECT_TIMEOUT,
                \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true],
                \GuzzleHttp\RequestOptions::VERIFY => \App::ZMS_AUTHORIZATION_SSL_VERIFY,
        ]);

        $this->provider = new Keycloak([
            'authServerUrl'         => \App::ZMS_AUTHORIZATION_AUTHSERVERURL,
            'realm'                 => \App::ZMS_AUTHORIZATION_REALM,
            'clientId'              => \App::ZMS_AUTHORIZATION_CLIENT_ID,
            'clientSecret'          => \App::ZMS_AUTHORIZATION_CLIENT_SECRET,
            'redirectUri'           => \App::ZMS_AUTHORIZATION_REDIRECTURI,
        ]);
        $this->request = $request;
        $this->provider->setHttpClient($guzzyClient);
    }

    public function Authorization(){
        $code = $this->request->getParam("code");
        if (!isset($code)) {
            // If we don't have an authorization code then get one
            $authUrl = $this->provider->getAuthorizationUrl();
            \BO\Zmsclient\Auth::setKey($this->provider->getState());
            header('Location: ' . $authUrl);
            exit;
        // Check given state against previously stored one to mitigate CSRF attack
        } elseif (empty($this->request->getParam("state")) || ($this->request->getParam("state") !== \BO\Zmsclient\Auth::getKey())) {
            \BO\Zmsclient\Auth::removeKey();
            exit('Invalid state.');
        } else {
            // Try to get an access token (using the authorization code grant)
            try {
                Profiler::add("start oidc");
                $token = $this->provider->getAccessToken('authorization_code', [
                    'code' => $code
                ]);
                Profiler::add("end oidc");
                error_log(\BO\Slim\Profiler::getList());
            } catch (Exception $e) {
                exit('Failed to get access token: '.$e->getMessage());
            }

            list($header, $payload, $signature)  = explode('.', $token->getToken());
            $this->accessTokenPayload = json_decode(base64_decode($payload), true);
        }
    }

    public function getUser()
    {
        try {
            $userAccount = new \BO\Zmsentities\Useraccount(array(
                'id' => "superuser",
                'password' => "vorschau",
                'departments' => array('id' => 0) // required in schema validation
            ));
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            \BO\Zmsclient\Auth::setKey($workstation->authkey);

            $user = array(
                "id" => $this->accessTokenPayload['preferred_username'],
                "email" => $this->accessTokenPayload['email'],
                "departments" => array(
                    "id" => 0,
                )
            );
            
            $entity = new Entity($user);
            $entity = $entity->withCleanedUpFormData(true);
            try {
                $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
            } catch (\BO\Zmsclient\Exception $exception) {
                throw $exception;
            }
            //Logout superuser
            \App::$http->readDeleteResult('/workstation/login/'. $workstation->useraccount['id'] .'/')->getEntity();

            return $entity;
        } catch (\Jumbojett\OpenIDConnectClientException $e) {
            throw $exception;
        }
    }

    public function checkAccessRight(){
        $resource_access_roles = $this->accessTokenPayload['resource_access'][\App::ZMS_AUTHORIZATION_CLIENT_ID]['roles'];
        return in_array( \App::ZMS_AUTHORIZATION_ACCESS_ROLE ,$resource_access_roles );
    }
}
