<?php

// Try to find autoload.php in various locations
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
} elseif (file_exists(__DIR__ . '/../../../../vendor/autoload.php')) {
    require __DIR__ . '/../../../../vendor/autoload.php';
} elseif (file_exists('vendor/autoload.php')) {
    require 'vendor/autoload.php';
} else {
    // Fallback for production where symlink in vendor/bin points here
    $path = realpath(__DIR__ . '/../../../../vendor/autoload.php');
    if ($path && file_exists($path)) {
        require $path;
    } else {
        die("Could not find autoload.php\n");
    }
}

require_once(__DIR__."/script_bootstrap.php");

use Garden\Cli\Cli;
use BO\Zmsdb\Config as ConfigRepository;

class DldbHelpers
{
    protected $cli;
    protected $config;
    protected $destinationPath;
    protected $backupPath;

    public function __construct($destinationPath, $cli = null)
    {
        $this->config = (new ConfigRepository())->readEntity();
        $this->cli = $cli ?: new Cli();
        $this->destinationPath = $destinationPath;
        $this->backupPath = $destinationPath . '/backups';
    }

    public function getBackupRetentionDays()
    {
        $envValue = getenv('ZMS_ENV');
        if ($envValue !== false) {
            $retentionSetting = explode(',', $this->config->getPreference('dldbBackup', 'setRetentionPeriodDays'));
            if ($retentionSetting[0] !== "none") {
                echo "Retention period is set in admin system config {$retentionSetting[0]} days.\n\n";
                return (int)$retentionSetting[0];
            }
        }
        echo "Using default rention period 7 days.\n\n";
        return 7;
    }

    public function getRollbackDay()
    {
        $envValue = getenv('ZMS_ENV');
        if ($envValue !== false) {
            $rollbackDaySetting = explode(',', $this->config->getPreference('dldbBackup', 'setRollbackDay'));
            if ($rollbackDaySetting[0] !== "none") {
                echo "Rollback day is set in admin system config to day {$rollbackDaySetting[0]}.\n\n";
                return (int)$rollbackDaySetting[0];
            }
        }
        echo "Using default \"none\" no rollback set.\n\n";
        return "none";
    }

    public function performRollback($rollbackDay)
    {
        if ($rollbackDay === "none") {
            return false;
        }

        echo "Rollback to day $rollbackDay is requested.\n\n";

        $backupDirectories = glob($this->backupPath . '/*', GLOB_ONLYDIR);
        usort($backupDirectories, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        if (isset($backupDirectories[$rollbackDay - 1])) {
            $rollbackDir = $backupDirectories[$rollbackDay - 1];
            echo "Rolling back using backup from: $rollbackDir\n\n";

            foreach (glob($rollbackDir . '/*.json') as $file) {
                $destFile = $this->destinationPath . '/' . basename($file);
                if (!copy($file, $destFile)) {
                    echo "Error: Failed to rollback $file to $destFile\n\n";
                } else {
                    echo "Rolled back $file to $destFile\n\n";
                }
            }
            return true;
        } else {
            echo "Error: Specified rollback day $rollbackDay does not exist in backups.\n\n";
            return false;
        }
    }

    public function checkAndCreateBackup($newFiles)
    {
        echo "Checking if backup is required.\n\n";
        $backupRequired = false;

        // Track files that need backup
        $filesToBackup = [];

        foreach ($newFiles as $filename => $filePath) {
            $destFile = $this->destinationPath . '/' . basename($filename);
            if (file_exists($destFile)) {
                // Compare existing file with new file
                if (md5_file($destFile) !== md5_file($filePath)) {
                    $backupRequired = true;
                    $filesToBackup[] = basename($destFile);
                }
            } else {
                // New file, check if any data exists
                $content = file_get_contents($filePath);
                if (!empty($content)) {
                    $backupRequired = true;
                }
            }
        }

        if ($backupRequired && !empty($filesToBackup)) {
            echo "Backup is required.\n\n";
            $timestamp = date('Y-m-d');
            $backupDir = $this->backupPath . '/' . $timestamp;

            if (!is_dir($backupDir) && !mkdir($backupDir, 0777, true)) {
                echo "Failed to create backup directory at $backupDir\n\n";
                exit(1);
            }

            // Backup all JSON files in destination
            foreach (glob($this->destinationPath . '/*.json') as $file) {
                if (!copy($file, $backupDir . '/' . basename($file))) {
                    echo "Failed to backup $file\n\n";
                }
            }
            echo "Backup created at: $backupDir\n\n";
        }

        return $backupRequired;
    }

    public function cleanupOldBackups()
    {
        echo "Fetching the backup retention period.\n\n";
        $retentionDays = $this->getBackupRetentionDays();
        $limitDate = time() - ($retentionDays * 24 * 60 * 60);
        
        foreach (glob($this->backupPath . '/*', GLOB_ONLYDIR) as $dir) {
            if (filemtime($dir) < $limitDate) {
                foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $item) {
                    if ($item->isDir()) {
                        rmdir($item->getRealPath());
                    } else {
                        unlink($item->getRealPath());
                    }
                }
                rmdir($dir);
                echo "Deleted old backup: $dir\n\n";
            }
        }
    }

    public function ensureDestinationDirectory()
    {
        if (!is_dir($this->destinationPath) && !mkdir($this->destinationPath, 0777, true) && !is_dir($this->destinationPath)) {
            echo "Failed to create the destination directory at $this->destinationPath\n\n";
            exit(1);
        }
    }
}

