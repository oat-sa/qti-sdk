<?php

namespace qtismtest\data\storage\php;

use InvalidArgumentException;
use qtism\data\storage\php\PhpArgument;
use qtism\data\storage\php\PhpVariable;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class PhpArgumentTest
 */
class PhpArgumentTest extends QtiSmTestCase
{
    public function testPhpArgument()
    {
        // Test a variable reference.
        $arg = new PhpArgument(new PhpVariable('test'));
        $this::assertInstanceOf(PhpArgument::class, $arg);
        $this::assertInstanceOf(PhpVariable::class, $arg->getValue());
        $this::assertTrue($arg->isVariableReference());
        $this::assertFalse($arg->isScalar());

        // Test a null value (considered to be scalar in this context).
        $arg = new PhpArgument(null);
        $this::assertInstanceOf(PhpArgument::class, $arg);
        $this::assertNull($arg->getValue());
        $this::assertFalse($arg->isVariableReference());
        $this::assertTrue($arg->isScalar());

        // Test a string value.
        $str = "Hello World!\nThis is me!";
        $arg = new PhpArgument($str);
        $this::assertInstanceOf(PhpArgument::class, $arg);
        $this::assertEquals($str, $arg->getValue());
        $this::assertFalse($arg->isVariableReference());
        $this::assertTrue($arg->isScalar());

        // Test a boolean value.
        $arg = new PhpArgument(false);
        $this::assertInstanceOf(PhpArgument::class, $arg);
        $this::assertFalse($arg->getValue());
        $this::assertFalse($arg->isVariableReference());
        $this::assertTrue($arg->isScalar());

        // Test an integer value.
        $arg = new PhpArgument(-23);
        $this::assertInstanceOf(PhpArgument::class, $arg);
        $this::assertEquals(-23, $arg->getValue());
        $this::assertFalse($arg->isVariableReference());
        $this::assertTrue($arg->isScalar());

        // Test a float value.
        $arg = new PhpArgument(-23.3);
        $this::assertInstanceOf(PhpArgument::class, $arg);
        $this::assertEquals(-23.3, $arg->getValue());
        $this::assertFalse($arg->isVariableReference());
        $this::assertTrue($arg->isScalar());
    }

    public function testObject()
    {
        $this->expectException(InvalidArgumentException::class);
        $arg = new PhpArgument(new stdClass());
    }
}
