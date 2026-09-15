<?php

namespace BO\Zmsdldb\Importer;

define('DEBUG', true);

class Timer
{
    protected float $start;
    protected float|null $pause = null;
    protected float|null $stop = null;
    protected float $elapsed = 0.0;

    public function __construct()
    {
        $this->start();
        if (true === DEBUG) {
            fwrite(STDERR, 'Working - please wait...' . PHP_EOL);
        }
    }

    public function start(): void
    {
        $this->start = Timer::getMicroTime();
    }

    /**
     * @psalm-api
     */
    public function stop(): void
    {
        $this->stop = Timer::getMicroTime();
    }

    /**
     * @psalm-api
     */
    public function pause(): void
    {
        $this->pause = Timer::getMicroTime();
        $this->elapsed += ($this->pause - $this->start);
    }

    /**
     * @psalm-api
     */
    public function resume(): void
    {
        $this->start = Timer::getMicroTime();
    }

    public function getTime(): string
    {
        if (!isset($this->stop)) {
            $this->stop = Timer::getMicroTime();
        }
        return $this->timeToString();
    }

    /** @psalm-api */
    protected function getLapTime(): string
    {
        return $this->timeToString();
    }

    protected static function getMicroTime(): float
    {
        list($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }

    protected function timeToString(): string
    {
        $seconds = ((float) ($this->stop ?? 0) - $this->start) + $this->elapsed;
        $seconds = Timer::roundMicroTime($seconds);
        $hours = floor($seconds / (60 * 60));
        $divisorForMinutes = $seconds % (60 * 60);
        $minutes = floor($divisorForMinutes / 60);
        return $hours . "h:" . $minutes . "m:" . $seconds . "s";
    }

    protected static function roundMicroTime(mixed $microTime): float
    {
        return round($microTime, 4, PHP_ROUND_HALF_UP);
    }

    public function __destruct()
    {
        if (true === DEBUG) {
            fwrite(STDERR, 'Job finished in ' . $this->getTime() . PHP_EOL);
        }
    }
}
