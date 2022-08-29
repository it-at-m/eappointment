<?php
/**
 *
 * @package Zmsappointment
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsadmin\Helper;

use \Stevenmaguire\OAuth2\Client\Provider\Keycloak as KeycloakProvider;

class KeyCloakAuth
{
    protected static $provider = null;

    protected static $accessToken = null;

    public function __construct()
    {
        static::$provider = $this->setProvider();
    }

    protected function getRefreshedAuthUrl()
    {
        $authUrl = static::$provider->getAuthorizationUrl();
        $_SESSION['oauth2state'] = static::$provider->getState();
        header('Location: '.$authUrl);
        exit;
    }

    protected function getRefreshedToken()
    {
        return static::$provider
            ->getAccessToken(
                'refresh_token',
                ['refresh_token' => static::$accessToken->getRefreshToken()]
            );
    }

    protected function testTokenByCode($code = '')
    {
        try {
            static::$accessToken = static::$provider->getAccessToken('authorization_code', [
                'code' => $code
            ]);
        } catch (Exception $e) {
            exit('Failed to get access token: '.$e->getMessage());
        }

        try {
            $user = static::$provider->getResourceOwner(static::$accessToken);
            //printf('Hello %s!', $user->getName());
        } catch (Exception $e) {
            exit('Failed to get resource owner: '.$e->getMessage());
        }
    }

    protected function setProvider()
    {
        static::$provider = new KeycloakProvider([
            'authServerUrl'         => 'http://192.168.59.103:32129/auth',
            'realm'                 => 'zms',
            'clientId'              => 'zmsadmin',
            'clientSecret'          => 'qSDZX60pht4XFLoKvSc8ouVMH96FvuNe',
            'redirectUri'           => 'https://192.168.59.103:32128/admin/workstation/select/',
            //'encryptionAlgorithm'   => 'RS256',                             // optional
            //'encryptionKeyPath'     => '../key.pem',                         // optional
            //'encryptionKey'         => 'contents_of_key_or_certificate'     // optional
        ]);
    }

    public function getToken()
    {
        static::$accessToken->getToken();
    }

    public function getProvider()
    {
        return static::$provider;
    }
}
