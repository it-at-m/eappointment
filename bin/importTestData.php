<?php

require(__DIR__ . '/../config.php');
$fixtures = realpath(__DIR__ . '/../tests/Zmsdb/fixtures/');

$execSqlFile = function ($file) {
    $pdo = BO\Zmsdb\Connection\Select::getWriteConnection();
    $startTime = microtime(true);
    //var_dump($pdo->fetchPairs('SHOW STATUS'));
    //var_dump($pdo->fetchAll('SHOW TABLES;'));
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
\BO\Zmsdb\Connection\Select::$writeSourceName = 'mysql:dbname=startinfo;host=127.0.0.1';
$execSqlFile($fixtures . '/mysql_startinfo.sql');
\BO\Zmsdb\Connection\Select::$writeSourceName = 'mysql:dbname=zmsbo;host=127.0.0.1';
\BO\Zmsdb\Connection\Select::closeWriteConnection();
$execSqlFile($fixtures . '/mysql_zmsbo.sql');
