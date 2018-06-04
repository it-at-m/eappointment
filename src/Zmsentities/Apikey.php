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
}
