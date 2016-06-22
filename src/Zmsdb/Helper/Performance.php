<?php

namespace BO\Zmsdb\Helper;

class Performance
{
    public static $times = [];

    public static function addMark()
    {
        array_push(self::$times, microtime(true));
    }

    public static function writeMark($message = 'Stopped time')
    {
        $lastTime = array_pop(self::$times);
        $timeDiff = microtime(true) - $lastTime;
        error_log("$message: " . $timeDiff);
        return $timeDiff;
    }
}
