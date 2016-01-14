<?php

namespace BO\Zmsclient\Tests;

use \BO\Mellon\Validator;

class HttpTest extends Base
{
    public function testStatus()
    {
        $http = $this->createHttpClient();
        $result = $http->readGetResult('/status/');
        $entity = $result->getEntity();
        $this->assertTrue($entity instanceof \BO\Zmsentities\Schema\Entity);
    }
}
