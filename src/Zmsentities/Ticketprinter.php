<?php

namespace BO\Zmsentities;

class Ticketprinter extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "ticketprinter.json";

    public function getHashWith($organisiationId)
    {
        return $organisiationId . bin2hex(openssl_random_pseudo_bytes(16));
    }
}
