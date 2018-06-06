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

    public function withRandomKey()
    {
        $this->key =  bin2hex(openssl_random_pseudo_bytes(16));
        return $this;
    }

    public function withCaptchaData($base64_png)
    {
        $this->captcha = new Mimepart([
            'content' => $base64_png,
            'mime' => 'image/png;base64',
            'base64' => true
        ]);
        return $this;
    }

    public function getQuotaPositionByRoute($route)
    {
        if (isset($this->quota)) {
            foreach ($this->quota as $position => $quota) {
                if ($route == $quota['route']) {
                    return $position;
                }
            }
        }
        return false;
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
