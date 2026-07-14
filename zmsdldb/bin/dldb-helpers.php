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
use BO\Zmsbackend\Config\Service\Config as ConfigRepository;

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
                    $val = $retentionSetting[0] ?? '';
                    if (strtolower((string)$val) !== 'none') {
                        if (ctype_digit((string)$val) && (int)$val > 0) {
                            \App::$log->info('Retention period set in admin system config', ['days' => (int) $val]);
                            return (int)$val;
                        }
                        \App::$log->warning('Invalid retention value, falling back to 7 days', ['value' => $val]);
                        return 7;
                    }
        }
        \App::$log->info('Using default retention period', ['days' => 7]);
        return 7;
    }

    public function getRollbackDay()
    {
        $envValue = getenv('ZMS_ENV');
        if ($envValue !== false) {
            $rollbackDaySetting = explode(',', $this->config->getPreference('dldbBackup', 'setRollbackDay'));
            if ($rollbackDaySetting[0] !== "none") {
                \App::$log->info('Rollback day set in admin system config', ['day' => (int) $rollbackDaySetting[0]]);
                return (int)$rollbackDaySetting[0];
            }
        }
        \App::$log->info('Using default no rollback');
        return "none";
    }

            public function performRollback($rollbackDay)
            {
                if ($rollbackDay === "none" || $rollbackDay === null || (int)$rollbackDay < 1) {
                    return false;
                }

                \App::$log->info('Rollback requested', ['day' => $rollbackDay]);

                // Ensure target paths exist
                $this->ensureDestinationDirectory();
                if (!is_dir($this->backupPath)) {
                    \App::$log->warning('No backups directory', ['path' => $this->backupPath]);
                    return false;
                }

                $backupDirectories = glob($this->backupPath . '/*', GLOB_ONLYDIR) ?: [];
                usort($backupDirectories, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });

                if (!isset($backupDirectories[$rollbackDay - 1])) {
                    \App::$log->error('Specified rollback day does not exist in backups', ['day' => $rollbackDay]);
                    return false;
                }

                $rollbackDir = $backupDirectories[$rollbackDay - 1];
                \App::$log->info('Rolling back using backup', ['backupDir' => $rollbackDir]);

                $hadError = false;
                foreach (glob($rollbackDir . '/*.json') as $file) {
                    $destFile = $this->destinationPath . '/' . basename($file);
                    if (!@copy($file, $destFile)) {
                        \App::$log->error('Failed to rollback file', ['source' => $file, 'destination' => $destFile]);
                        $hadError = true;
                    } else {
                        \App::$log->info('Rolled back file', ['source' => $file, 'destination' => $destFile]);
                    }
                }

                return $hadError ? false : true;
            }

    public function checkAndCreateBackup($newFiles)
    {
        \App::$log->info('Checking if backup is required');
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
            \App::$log->info('Backup is required');
            $timestamp = date('Y-m-d');
            $backupDir = $this->backupPath . '/' . $timestamp;

            if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
                throw new \RuntimeException("Failed to create backup directory at {$backupDir}");
            }

            // Backup all JSON files in destination
            foreach (glob($this->destinationPath . '/*.json') as $file) {
                if (!copy($file, $backupDir . '/' . basename($file))) {
                    \App::$log->error('Failed to backup file', ['file' => $file]);
                }
            }
            \App::$log->info('Backup created', ['backupDir' => $backupDir]);
        }

        return $backupRequired;
    }

    public function cleanupOldBackups()
    {
        \App::$log->info('Fetching the backup retention period');
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
                \App::$log->info('Deleted old backup', ['backupDir' => $dir]);
            }
        }
    }

            public function ensureDestinationDirectory()
            {
                if (!is_dir($this->destinationPath) && !mkdir($this->destinationPath, 0755, true) && !is_dir($this->destinationPath)) {
                    throw new \RuntimeException("Failed to create the destination directory at {$this->destinationPath}");
                }
            }
}

