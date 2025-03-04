<?php

namespace BO\Zmsapi\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

class Status extends Base
{
    /**
     * @SuppressWarnings(Parameter)
     * @codeCoverageIgnore
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $status = (new \BO\Zmsdb\Status())->readEntity(\App::$now);
        $climate->json($status);
    }
}
