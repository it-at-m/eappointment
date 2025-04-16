<?php

namespace BO\Zmsentities\Collection;

class MailtemplateList extends Base
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Mailtemplate';

    public function prioritizeByName(array $priorityNames)
    {
        $prioritized = [];
        $others = [];

        foreach ($this as $key => $mailtemplate) {
            $name = $mailtemplate->name;
            if (in_array($name, $priorityNames)) {
                $index = array_search($name, $priorityNames);
                $prioritized[$index] = $mailtemplate;
            } else {
                $others[$key] = $mailtemplate;
            }
        }

        ksort($prioritized);

        $mergedList = array_merge($prioritized, $others);

        $this->exchangeArray($mergedList);

        return $this;
    }
}
