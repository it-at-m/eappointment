<?php

declare(strict_types=1);

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
     * Fordert eine neue Captcha-Challenge an.
     *
     * @return array
     * @throws \Exception
     */
    public function createChallenge(): array;

    /**
     * Überprüft die Captcha-Lösung.
     *
     * @param string $payload
     * @return mixed
     * @throws \Exception
     */
    public function verifySolution(string $payload): array;
}
