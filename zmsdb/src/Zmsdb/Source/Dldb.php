<?php

namespace BO\Zmsdb\Source;

/**
 * Extends the base class to include functionality for managing data dumps,
 * including conditional backup based on changes and structured storage.
 */
class Dldb extends \BO\Zmsdb\Base
{
    public static $importPath = '';
    public static $backupPath = 'backups'; // Path to the backup directory
    public static $repository = null;
    public static $verbose = false;

    /**
     * Returns the path to import fixtures.
     *
     * @return string Path to the fixtures import directory.
     */
    public static function getFixturesImportPath()
    {
        $dir = dirname(__FILE__);
        return realpath($dir . '/../../../tests/Zmsdb/fixtures/');
    }

    /**
     * Initiates the import process, includes conditional backup and data processing for unit test data.
     *
     * @param bool $verbose Whether to log verbose output.
     */
    public function startTestImport($verbose = true)
    {
        if (!static::$importPath) {
            throw new \Exception('No data path given');
        }
        if ($verbose) {
            self::$verbose = $verbose;
            echo "Use source-path for dldb: ". static::$importPath . "\n";
        }
        self::$repository = new \BO\Dldb\FileAccess();
        self::$repository->loadFromPath(static::$importPath);

        \BO\Zmsdb\Connection\Select::setTransaction();

        $this->writeRequestList();
        $this->writeProviderList();
        $this->writeRequestRelationList();
        $this->writeLastUpdate($verbose);

        \BO\Zmsdb\Connection\Select::writeCommit();
    }

    /**
     * Initiates the import process, includes conditional backup and data processing for dldb mapper data.
     *
     * @param bool $verbose Whether to log verbose output.
     */
    public function startDLDBImport($verbose = true)
    {
        if (!static::$importPath) {
            throw new \Exception('No data path given');
        }

        if (static::$backupPath === '') {
            throw new \Exception('Backup path not specified');
        }

        if ($verbose) {
            self::$verbose = $verbose;
            echo "Use source-path for dldb: " . static::$importPath . "\n";
        }

        if ($this->isBackupRequired()) {
            $this->backupData();
        }

        self::$repository = new \BO\Dldb\FileAccess();
        self::$repository->loadFromPath(static::$importPath);

        \BO\Zmsdb\Connection\Select::setTransaction();

        $this->writeRequestList();
        $this->writeProviderList();
        $this->writeRequestRelationList();
        $this->writeLastUpdate($verbose);

        \BO\Zmsdb\Connection\Select::writeCommit();
    }

    /**
     * Checks if any file in the current dump is different from the last backup,
     * indicating that a backup is required.
     *
     * @return bool Whether a backup is required.
     */
    protected function isBackupRequired()
    {
        // Correctly construct the full path to the backup directory
        $fullBackupPath = rtrim(static::$importPath, '/') . '/' . ltrim(static::$backupPath, '/');
    
        // Use array_filter to exclude .json files from the backups/ subdirectory
        $files = array_filter(glob(static::$importPath . '*.json'), function ($file) use ($fullBackupPath) {
            return strpos($file, $fullBackupPath) === false;
        });
    
        $lastBackupDir = $this->getLastBackupDir();
    
        foreach ($files as $file) {
            $basename = basename($file);
            // Construct the path to the counterpart file in the last backup
            $backupFile = $lastBackupDir . '/' . $basename;
            
            // Check if the file exists in the last backup and compare MD5 hashes
            if (!file_exists($backupFile) || md5_file($file) !== md5_file($backupFile)) {
                return true; // Backup required if the file is new or different
            }
        }
    
        return false; // No backup needed if all files are unchanged
    }     

    /**
     * Backs up the current data to a new directory named with the current date.
     */
    protected function backupData()
    {
        // Construct the full backup path
        $fullBackupPath = rtrim(static::$importPath, '/') . '/' . ltrim(static::$backupPath, '/');
        if (!is_dir($fullBackupPath)) {
            mkdir($fullBackupPath, 0777, true);
            echo "Created root backup directory: " . $fullBackupPath . "\n";
        }
        
        $date = date('Y-m-d');
        $backupDir = $fullBackupPath . '/' . $date;
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0777, true);
            echo "Created backup directory for today: " . $backupDir . "\n";
        }
        
        $files = glob(static::$importPath . '*.json');
        foreach ($files as $file) {
            $basename = basename($file);
            rename($file, $backupDir . '/' . $basename);
            echo "Backed up " . $basename . " to " . $backupDir . '/' . $basename . "\n";
        }
    }
    

    /**
     * Returns the directory of the most recent backup.
     *
     * @return string The path to the last backup directory.
     */
    protected function getLastBackupDir()
    {
        // Construct the full backup path
        $fullBackupPath = rtrim(static::$importPath, '/') . '/' . ltrim(static::$backupPath, '/');
        $directories = glob($fullBackupPath . '/*', GLOB_ONLYDIR);
        usort($directories, function ($a, $b) {
            return filemtime($b) - filemtime($a);
        });
    
        return !empty($directories) ? array_shift($directories) : '';
    }    

    protected function writeRequestList()
    {
        $startTime = microtime(true);
        $requestQuery = (new \BO\Zmsdb\Request());
        $requestQuery->writeDeleteListBySource('dldb');
        foreach (self::$repository->fromService()->fetchList() as $request) {
            $topic = self::$repository->fromTopic()->fetchId($request['relation']['root_topic']);
            $request['group'] = $topic['name'];
            $requestQuery->writeImportEntity($request);
        }
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "Requests: Took $time seconds\n";
        }
    }

    protected function writeProviderList()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\Provider())->writeDeleteListBySource('dldb');
        $providers = (new \BO\Zmsdb\Provider())->writeImportList(self::$repository->fromLocation()->fetchList());

        foreach ($providers as $provider) {
            $providerData = $provider->data;

            if (!isset($providerData['forceSlotTimeUpdate']) || !$providerData['forceSlotTimeUpdate']) {
                continue;
            }

            $scopes = (new \BO\Zmsdb\Scope())->readByProviderId($provider->getId());
            foreach ($scopes as $scope) {
                $availabilities = (new \BO\Zmsdb\Availability())->readList($scope->getId());

                foreach ($availabilities as $availability) {
                    if ($availability->slotTimeInMinutes === $providerData['slotTimeInMinutes']) {
                        continue;
                    }

                    $availability->slotTimeInMinutes = $providerData['slotTimeInMinutes'];
                    $updatedEntity =
                        (new \BO\Zmsdb\Availability())->updateEntity($availability->getId(), $availability, 2);

                    (new \BO\Zmsdb\Slot)->writeByAvailability($updatedEntity, \App::$now);
                    (new \BO\Zmsdb\Helper\CalculateSlots(\App::DEBUG))
                        ->writePostProcessingByScope($updatedEntity->scope, \App::$now);
                }
            }
        }
        
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "Provider: Took $time seconds\n";
        }
    }

    protected function writeRequestRelationList()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\RequestRelation())->writeDeleteListBySource('dldb');
        (new \BO\Zmsdb\RequestRelation())->writeImportList(self::$repository->fromLocation()->fetchList());
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "RequestRelation: Took $time seconds\n";
        }
    }

    protected function writeLastUpdate()
    {
        $startTime = microtime(true);
        (new \BO\Zmsdb\Config())->replaceProperty('sources_dldb_last', date('c'));
        $time = round(microtime(true) - $startTime, 3);
        if (self::$verbose) {
            echo "LastImportTimeToConfig: Took $time seconds\n";
        }
    }
}
