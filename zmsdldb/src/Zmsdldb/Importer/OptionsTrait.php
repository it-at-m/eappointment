<?php

namespace BO\Zmsdldb\Importer;

trait OptionsTrait
{
    protected int $options = 0;

    protected function checkOptionFlag(int $optionFlag = 0): mixed
    {
        return $this->options & $optionFlag;
    }

    protected function setOptions(int $options = 0): void
    {
        $this->options = $options;
    }

    protected function getOptions(): int
    {
        return $this->options;
    }
}
