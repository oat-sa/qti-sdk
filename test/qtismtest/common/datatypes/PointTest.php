<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiPoint;
use qtismtest\QtiSmTestCase;

class PointTest extends QtiSmTestCase
{
    public function testEquality()
    {
        $p1 = new QtiPoint(10, 10);
        $p2 = new QtiPoint(10, 10);
        $p3 = new QtiPoint(100, 100);

        $this->assertEquals('10 10', $p1->getValue());
        $this->assertEquals('10 10', $p2->getValue());
        $this->assertEquals('100 100', $p3->getValue());

        $this->assertTrue($p1->equals($p2));
        $this->assertTrue($p2->equals($p1));
        $this->assertFalse($p1->equals($p3));
        $this->assertFalse($p3->equals($p1));
        $this->assertFalse($p3->equals(1337));
        $this->assertTrue($p3->equals($p3));
    }
}
