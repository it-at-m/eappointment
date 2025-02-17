<?php

namespace BO\Dldb\Importer;

require_once('Timer.php');

$timer = new Timer();


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
    SQLite::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES | SQLite::OPTION_CLEAR_ENTITIY_TABLE
);
try {
    $sqLiteIporter->beginTransaction();
    $sqLiteIporter->runImport();
    $sqLiteIporter->commit();
} catch (\Exception $e) {
    $sqLiteIporter->rollBack();
    error_log('Import faild');
}


unset($timer);
echo "Memory usage: " . number_format((memory_get_usage() / (1024 * 1024)), 2) . ' mb' . PHP_EOL;
