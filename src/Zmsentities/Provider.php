<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "provider.json";

    public function __construct($input = null, $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        $refString = '$ref';
        $providerId = isset($input['id']) ? $input['id'] : null;
        $providerRef = isset($input[$refString]) ? $input[$refString] : null;
        $providerId = ($providerId) ? $providerId : preg_replace('#^.*/(\d+)/$#', '$1', $providerRef);
        $input['id'] = $providerId;
        parent::__construct($this->getDefaults(), $flags, $iterator_class);
        $this->addData($input);
    }

    public function hasRequest($requestId)
    {
        return $this->getRequestList()->hasRequests($requestId);
    }

    public function getRequestList()
    {
        $requestList = new \BO\Zmsentities\Collection\RequestList();
        if (isset($this['data']['services'])) {
            foreach ($this['data']['services'] as $item) {
                $request = new Request([
                    'id' => $item['service'],
                    'source' => 'dldb',
                    'link' => isset($item['url']) ? $item['url'] : '',
                ]);
                $requestList->addEntity($request);
            }
        }
        return $requestList;
    }
}
