<?php

namespace BO\Zmsentities;

class Apikey extends Schema\Entity
{
    const PRIMARY = 'key';

    public static $schema = "apikey.json";

    public function getDefaults()
    {
        return [];
    }

    public function getHashFromCaptcha($text, $secret)
    {
        $hash = hash('sha256', $text . $secret);
        $hash = substr($hash, 29);
        return str_replace('/', '', $hash);
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
