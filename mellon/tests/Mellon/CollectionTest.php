<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Tests;

use BO\Mellon\Validator;

/**
  *
  *
  */
class CollectionTest extends \PHPUnit\Framework\TestCase
{
    public function testUsage()
    {
        $collection = Validator::collection(array(
            'name' => Validator::value('test')
                ->isString()
                ->setDefault('dummy'),
            'phone' => Validator::value('1234')
                ->isNumber()
                ->setDefault('11'),
        ));
        $this->assertFalse($collection->hasFailed(), 'Collection usage should validate');
        $form = $collection->getValues();
        $this->assertArrayHasKey('name', $form, 'Collection should return name');
    }

    public function testFail()
    {
        $collection = Validator::collection(array(
            'name' => Validator::value('test')
                ->isNumber()
        ));
        $this->assertTrue($collection->hasFailed(), 'Collection should fail');
    }

    public function testRecursive()
    {
        $collection = Validator::collection(array(
            'sub' => array(
                'name' => Validator::value('test')
                    ->isNumber()
            )
        ));
        $this->assertTrue($collection->hasFailed(), 'Recursive Collection should fail');
    }

    public function testNonvalid()
    {
        $this->expectException('\BO\Mellon\Exception');
        Validator::collection(array(
            'name' => Validator::value('test')
        ));
    }

    public function testMessages()
    {
        $collection = Validator::collection(array(
            'name' => Validator::value('test')->isNumber('testmessage'),
            'sub' => array(
                'phone' => Validator::value('1234')->isNumber(),
            ),
        ));
        $messages = $collection->getStatus();
        $this->assertEquals('testmessage', $messages['name']['messages'][0], 'Failed should return testmessage');
    }
}
