<?php

namespace BO\Dldb\Importer;

require_once('Timer.php');
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
require_once('OptionsTrait.php');
require_once('Options.php');
require_once('PDOTrait.php');
require_once('Base.php');
require_once('MySQL.php');
require_once('ItemNeedsUpdateTrait.php');
require_once('MySQL/Base.php');
require_once('MySQL/Authorities.php');
require_once('MySQL/Locations.php');
require_once('MySQL/Services.php');
require_once('MySQL/Settings.php');
require_once('MySQL/Topics.php');
require_once('MySQL/Entity/Collection.php');
require_once('MySQL/Entity/Base.php');
require_once('MySQL/Entity/Meta.php');
require_once('MySQL/Entity/Service.php');
require_once('MySQL/Entity/ServiceInformation.php');
require_once('MySQL/Entity/LocationService.php');
require_once('MySQL/Entity/Location.php');
require_once('MySQL/Entity/Contact.php');
require_once('MySQL/Entity/Authority.php');
require_once('MySQL/Entity/Setting.php');
require_once('MySQL/Entity/Topic.php');
require_once('MySQL/Entity/TopicCluster.php');
require_once('MySQL/Entity/TopicLinks.php');
require_once('MySQL/Entity/TopicService.php');



$timer = new Timer();

$fileAccess = new \BO\Dldb\FileAccess();

$fileAccess->loadFromPath(__DIR__ . '/../../../data/');

$mysqlIporter = new MySQL(
    new \BO\Dldb\MySQLAccess([]),
    $fileAccess
    #,MySQL::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES|MySQL::OPTION_CLEAR_ENTITIY_TABLE
);

try {
    $mysqlIporter->beginTransaction();
    $mysqlIporter->runImport();
    $mysqlIporter->commit();
} catch (\Exception $e) {
    $mysqlIporter->rollBack();
    error_log('Import faild - ' . $e->getMessage());
}

unset($timer);
echo "Memory usage: " . number_format((memory_get_usage() / (1024 * 1024)), 2) . ' mb' . PHP_EOL;
