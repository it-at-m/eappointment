<?php

namespace BO\Slim;

class Profiler
{
    public static $startupMicrotime = null;
    public static $profileList = [];

    /**
     * @SuppressWarnings(Superglobal)
     */
    public static function init(): void
    {
        static::$startupMicrotime = microtime(true);
        if (isset($_SERVER["REQUEST_TIME_FLOAT"])) {
            static::$startupMicrotime = $_SERVER["REQUEST_TIME_FLOAT"];
        }
    }

    public static function add(string $message): static
    {
        $profile = new static($message);
        static::$profileList[] = $profile;
        return $profile;
    }

    public static function addMemoryPeak($message = 'Mem'): void
    {
        $memoryKb = round(memory_get_peak_usage() / 1024, 0);
        static::add("$message " . $memoryKb . "kb");
    }

    public static function getList(): string
    {
        return implode(";", static::$profileList);
    }

    protected $message = '';
    protected $instanceMicrotime = null;
    protected $includedFiles = 0;

    public function __construct($message)
    {
        $this->message = $message;
        $this->instanceMicrotime = microtime(true);
        $this->includedFiles = count(get_included_files());
    }

    public function getSeconds(): float
    {
        return round(($this->instanceMicrotime - static::$startupMicrotime), 3);
    }

    public function getMilliSeconds()
    {
        return $this->getSeconds() * 1000;
    }

    public function __toString()
    {
        return $this->message . "=" . $this->getMilliSeconds() . "ms/#" . $this->includedFiles;
    }
}
