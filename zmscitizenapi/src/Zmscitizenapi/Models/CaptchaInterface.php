<?php

namespace BO\Zmscitizenapi\Models;

use BO\Zmsentities\Schema\Entity;

interface CaptchaInterface
{
    /**
     * Gibt die Captcha-Konfigurationsdetails zurück.
     *
     * @return array
     */
    public function getCaptchaDetails(): array;

    /**
     * Überprüft die Captcha-Lösung.
     *
     * @param string $solution
     * @return mixed
     * @throws \Exception
     */
    public function verifyCaptcha(string $solution);
}