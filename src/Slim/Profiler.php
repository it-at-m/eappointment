<?php

namespace BO\Slim;

class Profiler
{
    public static $startupMicrotime = null;
    public static $profileList = [];

    /**
     * @SuppressWarnings(Superglobal)
     *
     */
    public static function init()
    {
        if (isset($_SERVER["REQUEST_TIME_FLOAT"])) {
            static::$startupMicrotime = $_SERVER["REQUEST_TIME_FLOAT"];
        } else {
            static::$startupMicrotime = microtime(true);
        }
    }

    public static function add($message)
    {
        $profile = new static($message);
        static::$profileList[] = $profile;
        return $profile;
    }

    public static function addMemoryPeak($message = 'Mem')
    {
        $memoryKb = round(memory_get_peak_usage() / 1024, 0);
        static::add("$message " . $memoryKb . "kb");
    }

    public static function getList()
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
        $opcacheStatus = opcache_get_status(false);
        $this->includedFiles = count(get_included_files());
    }

    public function getSeconds()
    {
        return round(($this->instanceMicrotime - static::$startupMicrotime), 3);
    }

    public function getMilliSeconds()
    {
        return $this->getSeconds() * 1000;
    }

    public function __toString()
    {
        return $this->message . "=" . $this->getMilliSeconds() . "ms/#".$this->includedFiles;
    }
}
