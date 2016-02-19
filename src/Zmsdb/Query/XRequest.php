<?php
namespace BO\Zmsdb\Query;

class XRequest extends Base
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buergeranliegen';

    public function getEntityMapping()
    {
        return [
            'id' => 'xrequest.BuergeranliegenID',
            'process__id' => 'xrequest.BuergerID',
            'request__id' => 'xrequest.AnliegenID'
        ];
    }

    public function addConditionXRequestId($xrequestId)
    {
        $this->query->where('BuergeranliegenID', '=', $xrequestId);
        return $this;
    }
}
