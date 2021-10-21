<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiList;
use qtism\common\datatypes\QtiString;
use qtismtest\QtiSmTestCase;

class ListTest extends QtiSmTestCase
{
    public function testEquality()
    {
        $str1 = new QtiString("first");
        $str2 = new QtiString("second");

        $list1 = new QtiList('string', [$str1, $str2]);
        $list2 = new QtiList('string', [$str1, $str2]);
        $list3 = new QtiList('string', [$str2, $str1]);

        $this::assertTrue($list1->equals($list2));
        $this::assertTrue($list2->equals($list1));
        $this::assertTrue($list1->equals($list1));
        $this::assertFalse($list3->equals($list1));

        $int1 = new QtiInteger(5);
        $int2 = new QtiInteger(6);

        $list4 = new QtiList('integer', [$int1, $int2]);
        $list5 = new QtiList('integer', [$int1, $int2]);

        $this::assertTrue($list4->equals($list5));
    }

    public function testToString()
    {
        $str1 = new QtiString("first");
        $str2 = new QtiString("second");

        $list1 = new QtiList("string", [$str1, $str2]);
        $this::assertEquals("[first, second]", (string)$list1);
    }

    public function testSetBaseType()
    {
        $str1 = new QtiString("first");
        $list1 = new QtiList("string", [$str1]);

        $this::assertEquals(4, $list1->getBaseType());
    }
}