<?php

namespace BO\Zmsdb\Tests\Query\Builder\Dialect;

use PHPUnit\Framework\TestCase;
use BO\Zmsdb\Query\Builder\Dialect\ANSI;

class ANSITest extends TestCase
{
    /*
     * ---------------- Table Quoting ----------------------
     */

    public function testTableEmptyString()
    {
        $a = new ANSI();
        $this->assertEquals('', $a->quoteTable(''));
    }

    public function testTableNull()
    {
        $a = new ANSI();
        $this->assertEquals(null, $a->quoteTable(null));
    }

    public function testTableQuotingBasic()
    {
        $a = new ANSI();
        $this->assertEquals('"mytable"', $a->quoteTable('mytable'));
    }

    public function testTableQuoteMultipleComponents()
    {
        $a = new ANSI();
        $this->assertEquals('"mydb"."mytable"', $a->quoteTable('mydb.mytable'));
    }

    public function testTableQuoteHangingPeriod()
    {
        $a = new ANSI();
        $this->assertEquals('"mybad"', $a->quoteTable('mybad.'));
    }

    public function testTableDoubleQuoting()
    {
        $a = new ANSI();
        $this->assertEquals('"mytable"', $a->quoteTable('"mytable"'));

        $a = new ANSI();
        $this->assertEquals('"mydb"."mytable"', $a->quoteTable('"mydb"."mytable"'));
    }

    /*
     * ------------------ Field Quoting --------------------
     */

    public function testFieldEmptyString()
    {
        $a = new ANSI();
        $this->assertEquals('', $a->quoteField(''));
    }

    public function testFieldNull()
    {
        $a = new ANSI();
        $this->assertEquals(null, $a->quoteField(null));
    }

    public function testFieldQuotingBasic()
    {
        $a = new ANSI();
        $this->assertEquals('"myfield"', $a->quoteField('myfield'));
    }

    public function testFieldQuoteMultipleComponents()
    {
        $a = new ANSI();
        $this->assertEquals('"mytable"."myfield"', $a->quoteField('mytable.myfield'));
        $this->assertEquals('"mydb"."mytable"."myfield"', $a->quoteField('mydb.mytable.myfield'));
    }

    public function testFieldQuoteStar()
    {
        $a = new ANSI();
        $this->assertEquals('"mytable".*', $a->quoteField('mytable.*'));
    }

    public function testFieldQuoteHangingPeriod()
    {
        $a = new ANSI();
        $this->assertEquals('"mybad"', $a->quoteField('mybad.'));
    }

    public function testFieldDoubleQuoting()
    {
        $a = new ANSI();
        $this->assertEquals('"myfield"', $a->quoteTable('"myfield"'));

        $a = new ANSI();
        $this->assertEquals('"mydb"."mytable"."myfield"', $a->quoteTable('"mydb"."mytable"."myfield"'));
    }
}
