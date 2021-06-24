<?php

namespace BO\Dldb\Importer;
define('DEBUG', true);

class Timer
{
    protected $_start, $_pause, $_stop, $_elapsed;# = 0;
    
    public function __construct() {
        $this->start();
        if (true === DEBUG) {
            echo 'Working - please wait...' . PHP_EOL;
        }
    }

    public function start() {
        $this->_start = Timer::getMicroTime();
    }

    public function stop() {
        $this->_stop = Timer::getMicroTime();
    }

    public function pause() {
        $this->_pause = Timer::getMicroTime();
        $this->_elapsed += ($this->_pause - $this->_start);
    }

    public function resume() {
        $this->_start = Timer::getMicroTime();
    }

    public function getTime() {
        if (!isset($this->_stop)) {
            $this->_stop = Timer::getMicroTime();
        }
        return $this->timeToString();
    }

    protected function getLapTime() {
        return $this->timeToString();
    }

    protected static function getMicroTime( ) {
        list($usec, $sec) = explode(' ', microtime());
        return ((float) $usec + (float) $sec);
    }

    protected function timeToString() {
        $seconds = ($this->_stop - $this->_start) + $this->_elapsed;
        $seconds = Timer::roundMicroTime($seconds);
        $hours = floor($seconds / (60 * 60));
        $divisor_for_minutes = $seconds % (60 * 60);
        $minutes = floor($divisor_for_minutes / 60);
        return $hours . "h:" . $minutes . "m:" . $seconds . "s";
    }

    protected static function roundMicroTime($microTime) {
        return round($microTime, 4, PHP_ROUND_HALF_UP);
    }

    public function __destruct() {
        if (true === DEBUG) {
            echo 'Job finished in ' . $this->getTime() . PHP_EOL;
        }
    }
}
$timer = new Timer();
function p() {
    print_r(func_get_args());
}

require_once('../Exception.php');
require_once('../AbstractAccess.php');
require_once('../PDOAccess.php');
require_once('../File/Base.php');
require_once('../File/Authority.php');
require_once('../File/Service.php');
require_once('../File/Setting.php');
require_once('../File/Link.php');
require_once('../File/Location.php');
require_once('../File/Borough.php');
require_once('../File/Topic.php');
require_once('../File/Office.php');


require_once('../FileAccess.php');
require_once('../MySQLAccess.php');
require_once('../SQLiteAccess.php');
require_once('PDOTrait.php');
require_once('Options.php');
require_once('OptionsTrait.php');
require_once('Base.php');


require_once('ItemNeedsUpdateTrait.php');

require_once('MySQL.php');

require_once('MySQL/Base.php');
#require_once('MySQL/Contact.php');
require_once('MySQL/Authorities.php');
require_once('MySQL/Locations.php');
require_once('MySQL/Services.php');
require_once('MySQL/Settings.php');
#require_once('MySQL/Meta.php');
require_once('MySQL/Topics.php');
require_once('MySQL/Entity/Collection.php');
require_once('MySQL/Entity/Base.php');
require_once('MySQL/Entity/Meta.php');
require_once('MySQL/Entity/Service.php');
require_once('MySQL/Entity/Service_Information.php');
require_once('MySQL/Entity/Location_Service.php');
require_once('MySQL/Entity/Location.php');
require_once('MySQL/Entity/Contact.php');
require_once('MySQL/Entity/Authority.php');
require_once('MySQL/Entity/Setting.php');
require_once('MySQL/Entity/Topic.php');
require_once('MySQL/Entity/Topic_Cluster.php');
require_once('MySQL/Entity/Topic_Links.php');
require_once('MySQL/Entity/Topic_Service.php');


require_once('SQLite.php');
require_once('SQLite/Base.php');

require_once('SQLite/Authorities.php');
require_once('SQLite/Locations.php');
require_once('SQLite/Services.php');
require_once('SQLite/Settings.php');
require_once('SQLite/Topics.php');

require_once('SQLite/Entity/Collection.php');
require_once('SQLite/Entity/Base.php');
require_once('SQLite/Entity/Meta.php');
require_once('SQLite/Entity/Service.php');
require_once('SQLite/Entity/Service_Information.php');
require_once('SQLite/Entity/Location_Service.php');
require_once('SQLite/Entity/Location.php');
require_once('SQLite/Entity/Contact.php');
require_once('SQLite/Entity/Authority.php');
require_once('SQLite/Entity/Setting.php');
require_once('SQLite/Entity/Topic.php');
require_once('SQLite/Entity/Topic_Cluster.php');
require_once('SQLite/Entity/Topic_Links.php');
require_once('SQLite/Entity/Topic_Service.php');

$fileAccess = new \BO\Dldb\FileAccess();

$fileAccess->loadFromPath(__DIR__ . '/../../../data/');

$sqLiteIporter = new SQLite(
    new \BO\Dldb\SQLiteAccess(['databasePath' => __DIR__ . \DIRECTORY_SEPARATOR]), 
    $fileAccess,
    SQLite::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES|SQLite::OPTION_CLEAR_ENTITIY_TABLE
);
try {
    $sqLiteIporter->beginTransaction();
    $sqLiteIporter->runImport();
    $sqLiteIporter->commit();
}
catch (\Exception $e) {
    $sqLiteIporter->rollBack();
    error_log('Import faild');
}


unset($timer);
echo "Memory usage: " . number_format( (memory_get_usage() / (1024 * 1024)), 2) . ' mb' . PHP_EOL;