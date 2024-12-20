<?php

namespace BO\Zmscitizenapi\Models;

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
    public function verifyCaptcha(string $solution): bool;
}