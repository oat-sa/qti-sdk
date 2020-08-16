<?php

namespace qtismtest\common\utils;

use qtism\common\datatypes\QtiInteger;
use qtism\common\utils\Reflection;
use qtismtest\QtiSmTestCase;
use ReflectionClass;
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

    public function testNewInstanceWithArguments()
    {
        $clazz = new ReflectionClass('\Exception');
        $args = ['A message', 12];
        $instance = Reflection::newInstance($clazz, $args);

        $this->assertInstanceOf('\\Exception', $instance);
        $this->assertEquals('A message', $instance->getMessage());
        $this->assertEquals(12, $instance->getCode());
    }

    public function testNewInstanceWithoutArguments()
    {
        $clazz = new ReflectionClass('\stdClass');
        $instance = Reflection::newInstance($clazz);

        $this->assertInstanceOf('\\stdClass', $instance);
    }

    public function shortClassNameProvider()
    {
        return [
            ['SomeClass', 'SomeClass'],
            ['Class', "My\\Class"],
            ['Class', "My\\Super\\Class"],
            ['Class', "\\My\\Super\\Class"],

            ['stdClass', new stdClass()],
            ['QtiInteger', new QtiInteger(10)],

            ['My_Stupid_Class', 'My_Stupid_Class'],
            [false, 12],
            [false, null],
            [false, "\\"],
        ];
    }
}
