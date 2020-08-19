<?php

namespace qtismtest\common\datatypes;

use qtism\common\datatypes\QtiString;
use qtismtest\QtiSmTestCase;

class StringTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->setExpectedException(
            \InvalidArgumentException::class,
            'The String Datatype only accepts to store string values.'
        );
        $string = new QtiString(1337);
    }

    public function testEmptyString()
    {
        $string = new QtiString('');
        $this->assertEquals('', $string->getValue());
    }

    /**
     * @dataProvider equalProvider
     *
     * @param string $str
     * @param mixed $val
     */
    public function testEqual($str, $val)
    {
        $qtiString = new QtiString($str);
        $this->assertTrue($qtiString->equals($val));
    }

    public function equalProvider()
    {
        return [
            ['', null],
            ['', ''],
            ['', new QtiString('')],
            ['test', 'test'],
            ['test', new QtiString('test')],
        ];
    }

    /**
     * @dataProvider notEqualProvider
     *
     * @param string $str
     * @param mixed $val
     */
    public function testNotEqual($str, $val)
    {
        $qtiString = new QtiString($str);
        $this->assertFalse($qtiString->equals($val));
    }

    public function notEqualProvider()
    {
        return [
            ['test', null],
            ['', 'test'],
            ['', new QtiString('test')],
            ['test', ''],
            ['test', new QtiString('')],
            ['Test', 'test'],
            ['Test', new QtiString('test')],
        ];
    }
}
