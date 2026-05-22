<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class Performance
{
    public static $times = [];
    public static $counter = 0;

    public static function addMark()
    {
        array_push(self::$times, microtime(true));
    }

    public static function writeMark($message = 'Stopped time')
    {
        $lastTime = array_pop(self::$times);
        $timeDiff = microtime(true) - $lastTime;
                    \App::$log->debug($message, [
                'component' => 'CalculateSlots',
                'step' => static::$counter++,
                'elapsed' => $timeDiff,
                    ]);
        return $timeDiff;
    }
}
