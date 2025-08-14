<?php
/**
 * @package ClientDldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdldb\Tests\Entities;

use BO\Zmsdldb\FileAccess;

class TopicsTest extends \PHPUnit\Framework\TestCase
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
