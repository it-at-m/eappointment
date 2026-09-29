<?php

namespace BO\Zmsbackend\Mail\Exception;

class MailTemplateCustomizationNotFound extends \Exception
{
    protected $code = 404;

    protected $message =
        'Die ausgewählte angepasste E-Mail-Template-Version wurde nicht gefunden.';
}
