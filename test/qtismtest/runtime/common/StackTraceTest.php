<?php

namespace qtismtest\runtime\common;

use InvalidArgumentException;
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\runtime\common\StackTrace;
use qtism\runtime\common\StackTraceItem;
use qtismtest\QtiSmTestCase;

class StackTraceTest extends QtiSmTestCase
{
    public function testPop()
    {
        $stackTrace = new StackTrace();
        $stackTraceItem = new StackTraceItem(new BaseValue(BaseType::INTEGER, 0), 'pouet');
        $stackTrace[] = $stackTraceItem;

        $this->assertCount(1, $stackTrace);
        $this->assertSame($stackTraceItem, $stackTrace->pop());
        $this->assertCount(0, $stackTrace);
    }

    public function testToString()
    {
        $stackTrace = new StackTrace();
        $stackTraceItem = new StackTraceItem(new BaseValue(BaseType::INTEGER, 0), 'pouet');
        $stackTrace[] = $stackTraceItem;
        $stackTrace[] = $stackTraceItem;

        $this->assertEquals("pouet\npouet\n", $stackTrace . '');
    }

    public function testAddInvalidDataType()
    {
        $stackTrace = new StackTrace();
        $stackTraceItem = new BaseValue(BaseType::INTEGER, 0);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The StackTrace class only accepts to store StackTraceItem objects.");

        $stackTrace[] = $stackTraceItem;
    }
}
