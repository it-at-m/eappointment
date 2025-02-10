<?php

namespace BO\Dldb\Importer;

trait OptionsTrait
{
    protected $options = 0;

    protected function checkOptionFlag($optionFlag = 0)
    {
        return $this->options & $optionFlag;
    }

    protected function setOptions(int $options = 0)
    {
        $this->options = $options;
    }

    protected function getOptions(): int
    {
        return $this->options;
    }
}
