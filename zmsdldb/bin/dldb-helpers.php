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

function dldbLog(string $message, string $level = 'info', array $context = []): void
{
    if (class_exists('\App', false)) {
        \BO\Slim\Bootstrap::ensureLogger();
        if (\App::$log) {
            $level = \BO\Slim\Bootstrap::normalizeLogLevelName($level);
            \App::$log->{$level}($message, $context);
            return;
        }
    }

    $levelName = class_exists(\BO\Slim\Bootstrap::class, false)
        ? strtoupper(\BO\Slim\Bootstrap::normalizeLogLevelName($level))
        : strtoupper($level);
    fwrite(STDOUT, json_encode([
        'time_local' => (new \DateTime())->format('Y-m-d\TH:i:sP'),
        'application' => 'zms',
        'module' => 'zmsdldb',
        'message' => $message,
        'level' => $levelName,
        'context' => $context,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "\n");
}

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
                            dldbLog('Retention period set in admin system config', 'info', ['days' => (int) $val]);
                            return (int)$val;
                        }
                        dldbLog('Invalid retention value, falling back to 7 days', 'warning', ['value' => $val]);
                        return 7;
                    }
        }
        dldbLog('Using default retention period', 'info', ['days' => 7]);
        return 7;
    }

    public function getRollbackDay()
    {
        $envValue = getenv('ZMS_ENV');
        if ($envValue !== false) {
            $rollbackDaySetting = explode(',', $this->config->getPreference('dldbBackup', 'setRollbackDay'));
            if ($rollbackDaySetting[0] !== "none") {
                dldbLog('Rollback day set in admin system config', 'info', ['day' => (int) $rollbackDaySetting[0]]);
                return (int)$rollbackDaySetting[0];
            }
        }
        dldbLog('Using default no rollback', 'info');
        return "none";
    }

            public function performRollback($rollbackDay)
            {
                if ($rollbackDay === "none" || $rollbackDay === null || (int)$rollbackDay < 1) {
                    return false;
                }

                dldbLog('Rollback requested', 'info', ['day' => $rollbackDay]);

                // Ensure target paths exist
                $this->ensureDestinationDirectory();
                if (!is_dir($this->backupPath)) {
                    dldbLog('No backups directory', 'warning', ['path' => $this->backupPath]);
                    return false;
                }

                $backupDirectories = glob($this->backupPath . '/*', GLOB_ONLYDIR) ?: [];
                usort($backupDirectories, function($a, $b) {
                    return filemtime($b) - filemtime($a);
                });

                if (!isset($backupDirectories[$rollbackDay - 1])) {
                    dldbLog('Specified rollback day does not exist in backups', 'error', ['day' => $rollbackDay]);
                    return false;
                }

                $rollbackDir = $backupDirectories[$rollbackDay - 1];
                dldbLog('Rolling back using backup', 'info', ['backupDir' => $rollbackDir]);

                $hadError = false;
                foreach (glob($rollbackDir . '/*.json') as $file) {
                    $destFile = $this->destinationPath . '/' . basename($file);
                    if (!@copy($file, $destFile)) {
                        dldbLog('Failed to rollback file', 'error', ['source' => $file, 'destination' => $destFile]);
                        $hadError = true;
                    } else {
                        dldbLog('Rolled back file', 'info', ['source' => $file, 'destination' => $destFile]);
                    }
                }

                return $hadError ? false : true;
            }

    public function checkAndCreateBackup($newFiles)
    {
        dldbLog('Checking if backup is required', 'info');
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
            dldbLog('Backup is required', 'info');
            $timestamp = date('Y-m-d');
            $backupDir = $this->backupPath . '/' . $timestamp;

            if (!is_dir($backupDir) && !mkdir($backupDir, 0755, true)) {
                throw new \RuntimeException("Failed to create backup directory at {$backupDir}");
            }

            // Backup all JSON files in destination
            foreach (glob($this->destinationPath . '/*.json') as $file) {
                if (!copy($file, $backupDir . '/' . basename($file))) {
                    dldbLog('Failed to backup file', 'error', ['file' => $file]);
                }
            }
            dldbLog('Backup created', 'info', ['backupDir' => $backupDir]);
        }

        return $backupRequired;
    }

    public function cleanupOldBackups()
    {
        dldbLog('Fetching the backup retention period', 'info');
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
                dldbLog('Deleted old backup', 'info', ['backupDir' => $dir]);
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

