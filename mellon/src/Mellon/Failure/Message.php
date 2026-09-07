<?php

/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Failure;

/**
  *
  *
  */
class Message
{
    public string $message = '';

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function __toString()
    {
        return $this->message;
    }
}
