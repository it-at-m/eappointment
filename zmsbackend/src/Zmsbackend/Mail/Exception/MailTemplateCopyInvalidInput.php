<?php

namespace BO\Zmsbackend\Mail\Exception;

class MailTemplateCopyInvalidInput extends \Exception
{
    protected $code = 400;

    protected $message =
        'Für das Kopieren des E-Mail-Templates wurden ungültige Standorte angegeben.';
}
