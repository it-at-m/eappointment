<?php

/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Failure;

use BO\Mellon\Valid;

/**
 * @extends \ArrayObject<array-key, Message>
 */
class MessageList extends \ArrayObject
{
    public function offsetSet(mixed $key, mixed $value): void
    {
        parent::offsetSet($key, $value);
    }

    public function __toString()
    {
        $string = "Validation failed: ";
        foreach ($this as $message) {
            $string .= (string)$message . "\n";
        }
        return $string;
    }
}
