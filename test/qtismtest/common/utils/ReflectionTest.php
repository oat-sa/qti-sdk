<?php

declare(strict_types=1);

namespace qtismtest\common\utils;

use Exception;
use qtism\common\datatypes\QtiInteger;
use qtism\common\utils\Reflection;
use qtismtest\QtiSmTestCase;
use ReflectionClass;
use stdClass;

/**
 * Class ReflectionTest
 */
class ReflectionTest extends QtiSmTestCase
{
    /**
     * @dataProvider shortClassNameProvider
     * @param mixed $expected
     * @param mixed $object
     */
    public function testShortClassName($expected, $object): void
    {
        $this::assertSame($expected, Reflection::shortClassName($object));
    }

    public function testNewInstanceWithArguments(): void
    {
        $clazz = new ReflectionClass(Exception::class);
        $args = ['A message', 12];
        $instance = Reflection::newInstance($clazz, $args);

        $this::assertInstanceOf(Exception::class, $instance);
        $this::assertEquals('A message', $instance->getMessage());
        $this::assertEquals(12, $instance->getCode());
    }

    public function testNewInstanceWithoutArguments(): void
    {
        $clazz = new ReflectionClass(stdClass::class);
        $instance = Reflection::newInstance($clazz);

        $this::assertInstanceOf(stdClass::class, $instance);
    }

    /**
     * @return array
     */
    public function shortClassNameProvider(): array
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
