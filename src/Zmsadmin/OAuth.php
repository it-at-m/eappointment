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

    public function __construct($request)
    {
        $guzzyClient = new Client([
            'defaults' => [
                \GuzzleHttp\RequestOptions::CONNECT_TIMEOUT => 5,
                \GuzzleHttp\RequestOptions::ALLOW_REDIRECTS => true],
                \GuzzleHttp\RequestOptions::VERIFY => false,
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
                    'code' => $_GET['code']
                ]);
                Profiler::add("end oidc");
                error_log("____________Profiler________________");
                error_log(\BO\Slim\Profiler::getList());
            } catch (Exception $e) {
                exit('Failed to get access token: '.$e->getMessage());
            }
            error_log("____________________________________GGGGGGGGGGGGGGGGGG___________________________________: ");
            // Use this to interact with an API on the users behalf
            $accessTokenPayload = json_decode($this->getAccessTokenPayload($token->getToken()), true);
            
            //TODO: double Code from index.php
            try {
                $workstation = \App::$http->readGetResult('/workstation/')->getEntity();
            } catch (\Exception $workstationexception) {
                $workstation = null;
            }
            error_log("____________________________________JJJJJJJJ___________________________________: " .  $accessTokenPayload);
            if($workstation === null){
                $this->createUser($accessTokenPayload);
            }
        }
    }

    protected function getAccessTokenPayload($jwt_access_token)
    {
        $separator = '.';

        if (2 !== substr_count($jwt_access_token, $separator)) {
            exit('Incorrect access token format.');
        }

        list($header, $payload, $signature) = explode($separator, $jwt_access_token);

        return base64_decode($payload);
    }

    protected function createUser($accessTokenPayload)
    {
        try {
            $userAccount = new \BO\Zmsentities\Useraccount(array(
                'id' => "superuser",
                'password' => "vorschau",
                'departments' => array('id' => 0) // required in schema validation
            ));
            $workstation = \App::$http->readPostResult('/workstation/login/', $userAccount)->getEntity();
            \BO\Zmsclient\Auth::setKey($workstation->authkey);
    
            error_log("____________________________________FFFFFFFFFFFFFFFFFFFF___________________________________: ");
            $newUser = array(
                "lastLogin" => 1447926465,
                "id" => $accessTokenPayload['preferred_username'],
                "email" => $accessTokenPayload['email'],
                "password" => $accessTokenPayload['sub'],
                "rights" => array(
                    "scope" => true,
                    "ticketprinter" => true
                ),
                "departments" => array(
                    "id" => 72,
                )
            );
            
            $entity = new Entity($newUser);
            $entity = $entity->withCleanedUpFormData(true);
            error_log("____________________________________SSSSSSSSSSSSSSSSSSSS___________________________________: ");
            try {
                $entity = \App::$http->readPostResult('/useraccount/', $entity)->getEntity();
            } catch (\BO\Zmsclient\Exception $exception) {
            }
            error_log("____________________________________ZZZZZZZZZZZZZZZZZZZ___________________________________: ");
            //Logout superuser
            \App::$http->readDeleteResult('/workstation/login/'. $workstation->useraccount['id'] .'/')->getEntity();
            error_log("____________________________________YYYYYYYYYYYYYYY___________________________________: ");
            $workstation = \App::$http->readPostResult('/workstation/oauth/', $entity)->getEntity();
            error_log("____________________________________DDDDDDDDDDDDDDDD___________________________________: ");
            \BO\Zmsclient\Auth::setKey($workstation->authkey);
        } catch (\Jumbojett\OpenIDConnectClientException $e) {
            throw $exception;
        }
    }

    public function logout(){
        header('Location: ' . $this->provider->getLogoutUrl());
        exit;
    }
}
