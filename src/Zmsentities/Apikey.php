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
