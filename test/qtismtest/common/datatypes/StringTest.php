<?php

namespace qtismtest\common\datatypes;

use InvalidArgumentException;
use qtism\common\datatypes\QtiString;
use qtismtest\QtiSmTestCase;

/**
 * Class StringTest
 *
 * @package qtismtest\common\datatypes
 */
class StringTest extends QtiSmTestCase
{
    public function testWrongValue()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The String Datatype only accepts to store string values.');
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

    /**
     * @return array
     */
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

    /**
     * @return array
     */
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
