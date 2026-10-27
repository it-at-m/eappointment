<?php

declare(strict_types=1);

namespace BO\Slim\Helper;

/**
 * Run a handful of PHP workers and wait until every one has finished.
 */
final class ProcessPool
{
    public static function workerCount(): int
    {
        $raw = getenv('ZMS_CACHE_WARMUP_WORKERS');
        if ($raw === false || $raw === '') {
            return 4;
        }

        $count = (int) $raw;

        return $count > 0 ? $count : 1;
    }

    public static function phpBinary(): string
    {
        $binary = PHP_BINARY;
        $base = strtolower(basename($binary));
        if ($binary !== '' && is_executable($binary) && !str_contains($base, 'fpm') && !str_contains($base, 'httpd')) {
            return $binary;
        }

        return 'php';
    }

    /**
     * @param list<list<string>> $commands
     * @return list<int> indexes of commands that did not exit 0
     */
    public static function run(array $commands, string $cwd): array
    {
        if (!function_exists('proc_open') || $commands === []) {
            return array_keys($commands);
        }

        $running = [];
        $failed = [];
        foreach ($commands as $index => $command) {
            $pipes = [];
            $process = proc_open(
                $command,
                [
                    0 => ['pipe', 'r'],
                    1 => ['pipe', 'w'],
                    2 => ['pipe', 'w'],
                ],
                $pipes,
                $cwd
            );
            if (!is_resource($process)) {
                $failed[] = $index;
                continue;
            }
            fclose($pipes[0]);
            stream_set_blocking($pipes[1], false);
            stream_set_blocking($pipes[2], false);
            $running[$index] = [
                'process' => $process,
                'stdout' => $pipes[1],
                'stderr' => $pipes[2],
            ];
        }

        while ($running !== []) {
            $read = [];
            foreach ($running as $worker) {
                if (is_resource($worker['stdout'])) {
                    $read[] = $worker['stdout'];
                }
                if (is_resource($worker['stderr'])) {
                    $read[] = $worker['stderr'];
                }
            }
            $write = null;
            $except = null;
            if ($read !== []) {
                @stream_select($read, $write, $except, 1);
            }

            foreach ($running as $index => $worker) {
                self::forward($worker['stdout'], STDOUT);
                self::forward($worker['stderr'], STDERR);
                $status = proc_get_status($worker['process']);
                if ($status['running']) {
                    continue;
                }
                self::forward($worker['stdout'], STDOUT);
                self::forward($worker['stderr'], STDERR);
                fclose($worker['stdout']);
                fclose($worker['stderr']);
                proc_close($worker['process']);
                if ($status['exitcode'] !== 0) {
                    $failed[] = $index;
                }
                unset($running[$index]);
            }
        }

        sort($failed);

        return $failed;
    }

    /**
     * @param resource $from
     * @param resource $to
     */
    private static function forward($from, $to): void
    {
        if (!is_resource($from)) {
            return;
        }
        $chunk = stream_get_contents($from);
        if (is_string($chunk) && $chunk !== '') {
            fwrite($to, $chunk);
        }
    }
}
