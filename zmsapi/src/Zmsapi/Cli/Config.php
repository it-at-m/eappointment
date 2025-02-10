<?php

namespace BO\Zmsapi\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

/**
 * @codeCoverageIgnore
 *
 */
class Config extends Base
{
    /**
     * @SuppressWarnings(Parameter)
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $config = (new \BO\Zmsdb\Config())->readEntity();
        if (count($argv) >= 3) {
            $type = $argv[2];
            $this->testType($config, $type);
        }
        if (count($argv) >= 4) {
            $key = $argv[3];
            $this->testKey($config, $type, $key);
        }
        if (count($argv) == 2) {
            $climate->green("#CONFIGURATION:");
            $climate->green("\tUsage: config [type] [key] [newvalue]");
            $config->ksort();
            array_walk($config, function ($subconfig, $type) use ($climate) {
                $this->printType($climate, $type, $subconfig);
            });
        } elseif (count($argv) == 3) {
            $subconfig = $config[$type];
            $this->printType($climate, $type, $subconfig);
        } elseif (count($argv) == 4) {
            $climate->out($config[$type][$key]);
        } elseif (count($argv) == 5) {
            $value = $argv[4];
            $climate->yellow("Old value:");
            $this->printValue($climate, $key, $config[$type][$key]);
            $config->setPreference($type, $key, $value);
            $climate->green("New value:");
            $this->printValue($climate, $key, $config[$type][$key]);
            if ((new \BO\Zmsdb\Config())->updateEntity($config)) {
                $climate->green("Database entry changed");
            }
        } elseif (count($argv) > 5) {
            throw new \Exception("Too much parameters");
        }
    }

    protected function testType($config, $type)
    {
        if (!$config->hasType($type)) {
            throw new \Exception("Could not find type of '$type'");
        }
    }

    protected function testKey($config, $type, $key)
    {
        if (!$config->hasPreference($type, $key)) {
            throw new \Exception("Could not find preference of '$type.$key'");
        }
    }

    protected function printType(\League\CLImate\CLImate $climate, $type, $subconfig)
    {
        $climate->blue("[$type]");
        if (is_array($subconfig)) {
            ksort($subconfig);
            array_walk($subconfig, function ($value, $key) use ($climate) {
                $this->printValue($climate, $key, $value);
            });
        }
    }

    protected function printValue(\League\CLImate\CLImate $climate, $key, $value)
    {
        $padding = $climate->padding(25)->char(' ');
        $padding->label("\"$key\"")->result("= \"" . (string)$value . '"');
    }
}
