<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Dldb\Tests\Entities;

use BO\Dldb\FileAccess;

class TopicsTest extends Base
{
    public function testBasic()
    {
        $access = new FileAccess();
        $access->loadFromPath(FIXTURES);
        $topicList = $access->fromTopic()->fetchList();
        $this->assertTrue(count($topicList) > 10);
        $topic = $access->fromTopic()->fetchPath('wirtschaft');
        $this->assertNotFalse($topic);
    }
}
