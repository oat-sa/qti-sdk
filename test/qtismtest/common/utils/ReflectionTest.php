<?php

use qtism\common\datatypes\QtiInteger;
use qtism\common\utils\Reflection;

require_once(dirname(__FILE__) . '/../../../QtiSmTestCase.php');

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
