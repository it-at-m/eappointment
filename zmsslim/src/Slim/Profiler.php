<?php

namespace BO\Slim;

class Profiler
{
    public static ?float $startupMicrotime = null;

    /** @var list<self> */
    public static array $profileList = [];

    protected string $message = '';

    protected float $instanceMicrotime;

    protected int $includedFiles = 0;

    /**
     * @SuppressWarnings(Superglobal)
     */
    public static function init(): void
    {
        static::$startupMicrotime = microtime(true);
        if (isset($_SERVER["REQUEST_TIME_FLOAT"])) {
            static::$startupMicrotime = (float) $_SERVER["REQUEST_TIME_FLOAT"];
        }
    }

    public static function add(string $message): void
    {
        static::$profileList[] = new self($message);
    }

    public static function addMemoryPeak(string $message = 'Mem'): void
    {
        $memoryKb = round(memory_get_peak_usage() / 1024, 0);
        static::add("$message " . $memoryKb . "kb");
    }

    public static function getList(): string
    {
        return implode(";", static::$profileList);
    }

    public function __construct(string $message)
    {
        $this->message = $message;
        $this->instanceMicrotime = microtime(true);
        $this->includedFiles = count(get_included_files());
    }

    public function getSeconds(): float
    {
        return round(($this->instanceMicrotime - (static::$startupMicrotime ?? $this->instanceMicrotime)), 3);
    }

    public function getMilliSeconds(): float
    {
        return $this->getSeconds() * 1000;
    }

    public function __toString(): string
    {
        return $this->message . "=" . $this->getMilliSeconds() . "ms/#" . $this->includedFiles;
    }
}
