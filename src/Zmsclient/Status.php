<?php
namespace BO\Zmsclient;

/**
 * Healthcheck concerning the API
 */
class Status
{
    /**
     * throws exception on critical status variables
     *
     */
    public static function testStatus($status)
    {
        return $status;
    }
}
