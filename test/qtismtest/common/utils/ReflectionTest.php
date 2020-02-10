<?php

namespace qtismtest\common\utils;

use qtism\common\datatypes\QtiInteger;
use qtism\common\utils\Reflection;
use qtismtest\QtiSmTestCase;
use stdClass;

class ReflectionTest extends QtiSmTestCase
{
    /**
     * @dataProvider shortClassNameProvider
     * @param mixed $expected
     * @param mixed $object
     */
    public function testShortClassName($expected, $object)
    {
        $this->assertSame($expected, Reflection::shortClassName($object));
    }

    public function shortClassNameProvider()
    {
        return [
            ["SomeClass", "SomeClass"],
            ["Class", "My\\Class"],
            ["Class", "My\\Super\\Class"],
            ["Class", "\\My\\Super\\Class"],

            ["stdClass", new stdClass()],
            ["QtiInteger", new QtiInteger(10)],

            ["My_Stupid_Class", "My_Stupid_Class"],
            [false, 12],
            [false, null],
            [false, "\\"],
        ];
    }
}
