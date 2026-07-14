<?php

namespace BO\Zmsbackend\Cli;

use Ulrichsg\Getopt\Getopt;
use Ulrichsg\Getopt\Option;

class Status extends \BO\Zmsbackend\Base
{
    /**
     * @SuppressWarnings(Parameter)
     * @codeCoverageIgnore
     *
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate)
    {
        $status = (new \BO\Zmsbackend\Status\Service\Status())->readEntity(\App::$now);
        $climate->json($status);
    }
}
