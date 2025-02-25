<?php

namespace BO\Zmsentities;

class Apikey extends Schema\Entity
{
    public const PRIMARY = 'key';

    public static $schema = "apikey.json";

    public function getDefaults()
    {
        return [
            'apiclient' => new Apiclient(),
        ];
    }

    public function setApiClient(Apiclient $apiClient)
    {
        $this['apiclient'] = $apiClient;
    }

    public function getApiClient(): Apiclient
    {
        return $this['apiclient'];
    }

    /**
     * @param String $string usually the captcha text or a token
     * @param String $secret use a secret to avoid crafted api keys
     */
    public function getHash($string, $secret = '4Td8x5Qn5hjW3uSc6MWWVQPwrw6b74fL')
    {
        $hash = password_hash($string . $secret, PASSWORD_BCRYPT);
        $hash = substr($hash, 7);
        return base64_encode($hash);
    }

    /**
     * @param String $text usually the captcha text
     * @param String $hash usually the apikey
     * @param String $secret has to be same secret used by self::getHashFromCaptcha()
     */
    public function isVerifiedHash($text, $hash, $secret = '4Td8x5Qn5hjW3uSc6MWWVQPwrw6b74fL')
    {
        return password_verify($text . $secret, '$2y$10$' . base64_decode($hash));
    }

    public function withCaptchaData($base64_jpg)
    {
        $this->captcha = new Mimepart([
            'content' => $base64_jpg,
            'mime' => 'image/jpeg;base64',
            'base64' => true
        ]);
        return $this;
    }

    public function getQuotaPositionByRoute($route)
    {
        return (isset($this->quota) && is_array($this->quota)) ?
            array_search($route, array_column($this->quota, 'route'))
            : false;
    }

    public function addQuota($route, $period)
    {
        $this->quota[] = [
            'route' => $route,
            'period' => $period,
            'requests' => 1
        ];
        return $this;
    }

    public function updateQuota($position)
    {
        $this->quota[$position]['requests']++;
        return $this;
    }
}
