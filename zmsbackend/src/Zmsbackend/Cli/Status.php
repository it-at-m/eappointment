<?php

namespace BO\Zmsbackend\Cli;

class Status extends \BO\Zmsbackend\Base
{
    /**
     * @SuppressWarnings(Parameter)
     *
     * @codeCoverageIgnore
     */
    public function cli(array $argv, \League\CLImate\CLImate $climate): void
    {
        $status = (new \BO\Zmsbackend\Status\Service\Status())->readEntity(\App::$now);
        $climate->json($status);
    }
}
