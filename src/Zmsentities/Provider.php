<?php

namespace BO\Zmsentities;

class Provider extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "provider.json";

    public function __construct($input = null, $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        $refString = '$ref';
        if ((is_array($input) || $input instanceof \ArrayAccess)
            && isset($input[$refString])
            && (!isset($input['id']) || !isset($input['source']))
        ) {
            $providerRef = $input[$refString];
            $providerId = preg_replace('#^.*/(\d+)/$#', '$1', $providerRef);
            $input['id'] = $providerId;
            $input['source'] = preg_replace('#^.*provider/([^/]+)/\d+/$#', '$1', $providerRef);
        }
        parent::__construct($input, $flags, $iterator_class);
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
