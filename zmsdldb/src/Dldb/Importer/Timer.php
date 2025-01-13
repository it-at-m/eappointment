<?php

namespace BO\Dldb\Importer;

define('DEBUG', true);

class Timer
{
    protected $start;
    protected $pause;
    protected $stop;
    protected $elapsed;# = 0;

    public function __construct()
    {
        $this->start();
        if (true === DEBUG) {
            echo 'Working - please wait...' . PHP_EOL;
        }
    }

    public function start()
    {
        $this->start = Timer::getMicroTime();
    }

    public function stop()
    {
        $this->stop = Timer::getMicroTime();
    }

    public function pause()
    {
        $this->pause = Timer::getMicroTime();
        $this->elapsed += ($this->pause - $this->start);
    }

    public function resume()
    {
        $this->start = Timer::getMicroTime();
    }

    public function getTime()
    {
        if (!isset($this->stop)) {
            $this->stop = Timer::getMicroTime();
        }
        return $this->timeToString();
    }

    protected function getLapTime()
    {
        return $this->timeToString();
    }

    protected static function getMicroTime()
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }

    protected function timeToString()
    {
        $seconds = ($this->stop - $this->start) + $this->elapsed;
        $seconds = Timer::roundMicroTime($seconds);
        $hours = floor($seconds / (60 * 60));
        $divisorForMinutes = $seconds % (60 * 60);
        $minutes = floor($divisorForMinutes / 60);
        return $hours . "h:" . $minutes . "m:" . $seconds . "s";
    }

    protected static function roundMicroTime($microTime)
    {
        return round($microTime, 4, PHP_ROUND_HALF_UP);
    }

    public function __destruct()
    {
        if (true === DEBUG) {
            echo 'Job finished in ' . $this->getTime() . PHP_EOL;
        }
    }
}

function print_rFnArgs()
{
    print_r(func_get_args());
}
