<?php

namespace BO\Zmsentities;

class Apikey extends Schema\Entity
{
    const PRIMARY = 'key';

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

    public function getHashFromCaptcha($text)
    {
        $hash = password_hash($text, PASSWORD_BCRYPT);
        $hash = substr($hash, 7);
        return base64_encode($hash);
    }

    public function isCaptchaFromHash($text, $hash)
    {
        return password_verify($text, '$2y$10$' . base64_decode($hash));
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
