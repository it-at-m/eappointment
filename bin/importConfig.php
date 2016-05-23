<?php

require(__DIR__ . '/../config.php');
$dbname_zms =& \BO\Zmsdb\Connection\Select::$dbname_zms;
$migrations = realpath(__DIR__ . '/../migrations/');

$execSqlFile = function ($file) {
    $pdo = BO\Zmsdb\Connection\Select::getWriteConnection();
    $startTime = microtime(true);
    $sqlFile = fopen($file, 'r');
    echo "Importing " . basename($file) . "\n";
    $query = '';
    while ($line = fgets($sqlFile)) {
        $query .= $line;
        if (false !== strpos($line, ';')) {
            try {
                $pdo->exec($query);
                echo ".";
                //echo "Successful:\n$query\n";
                $query = '';
            } catch (Exception $exception) {
                echo "Offending query: \n$query\n";
                throw $exception;
            }
        }
    }
    $time = round(microtime(true) - $startTime, 3);
    echo "\nTook $time seconds\n";
};

$execSqlFile($migrations . '/28300-create-config-table.sql');

$defaults = new \BO\Zmsentities\Config();
$config = (new \BO\Zmsdb\Config())->updateEntity($defaults);
