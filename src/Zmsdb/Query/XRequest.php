<?php
namespace BO\Zmsdb\Query;

class XRequest extends Base
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buergeranliegen';

    const QUERY_DELETE = "DELETE FROM `buergeranliegen` WHERE `BuergerID` = ?";

    public function getEntityMapping()
    {
        return [
            'id' => 'xrequest.BuergeranliegenID',
            'process__id' => 'xrequest.BuergerID',
            'request__id' => 'xrequest.AnliegenID'
        ];
    }

    public function addConditionXRequestId($requestId)
    {
        $this->query->where('AnliegenID', '=', $requestId);
        return $this;
    }

}
