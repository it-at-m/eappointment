<?php

namespace BO\Zmsdb\Query;

class Source extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'source';

    public function getEntityMapping()
    {
        return [
            'source' => 'source.source',
            'label' => 'source.label',
            'editable' => 'source.editable',
            'contact__name' => 'source.contact__name',
            'contact__email' => 'source.contact__email',
            'lastChange' => 'source.lastChange',
        ];
    }

    public function addConditionSource($source)
    {
        $this->query->where('source.source', '=', $source);
        return $this;
    }
}
