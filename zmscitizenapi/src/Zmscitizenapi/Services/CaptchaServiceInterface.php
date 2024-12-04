<?php

namespace BO\Zmscitizenapi\Services;

interface CaptchaServiceInterface
{
    /**
     * Gibt die Captcha-Konfigurationsdetails zurück.
     *
     * @return array
     */
    public static function getCaptchaDetails(): array;

    /**
     * Überprüft die Captcha-Lösung.
     *
     * @param string $solution
     * @return mixed
     * @throws \Exception
     */
    public static function verifyCaptcha(string $solution);
}
