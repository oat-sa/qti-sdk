<?php

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiPair;
use qtismtest\QtiSmTestCase;

/**
 * Class PairTest
 */
class PairTest extends QtiSmTestCase
{
    public function testEquality()
    {
        $p1 = new QtiPair('A', 'B');
        $p2 = new QtiPair('A', 'B');
        $p3 = new QtiPair('C', 'D');
        $p4 = new QtiPair('D', 'C');

        $this->assertTrue($p1->equals($p2));
        $this->assertTrue($p2->equals($p1));
        $this->assertFalse($p1->equals($p3));
        $this->assertFalse($p3->equals($p1));
        $this->assertFalse($p3->equals(1337));
        $this->assertTrue($p3->equals($p3));
        $this->assertTrue($p4->equals($p3));
    }

    public function testInvalidFirstIdentifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $pair = new QtiPair('_33', '33tt');
    }

    public function testInvalidSecondIdentifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $pair = new QtiPair('33tt', '_33');
    }
}
