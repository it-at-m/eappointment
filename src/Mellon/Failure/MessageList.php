<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Failure;

use BO\Mellon\Valid;

/**
  *
  *
  */
class MessageList extends \ArrayObject
{
    public function __toString()
    {
        $string = "Validation failed: ";
        foreach ($this as $message) {
            $string .= (string)$message . "\n";
        }
        return $string;
    }
}
