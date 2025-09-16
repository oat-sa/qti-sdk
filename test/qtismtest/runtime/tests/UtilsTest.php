<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\state\ResponseValidityConstraint;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\tests\Utils;
use qtismtest\QtiSmTestCase;

class UtilsTest extends QtiSmTestCase
{
    /**
     * Test that newlines are stripped from single string values during pattern validation
     */
    public function testIsResponseValidWithNewlinesInSingleString()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^.{5}$');

        $response = new QtiString("hel\nlo");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("hel\nl");
        $this->assertFalse(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test newline normalization with multiple types of newlines
     */
    public function testIsResponseValidWithDifferentNewlineTypes()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^test$');

        $response = new QtiString("te\nst");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("t\ne\ns\nt");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test that normalization works with length-based patterns
     */
    public function testIsResponseValidWithLengthPattern()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^.{10}$');

        $response = new QtiString("hello\nwor\nld");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("hello\nwo\nrl");
        $this->assertFalse(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test word-based pattern matching with newlines
     */
    public function testIsResponseValidWithWordPattern()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^hello$');

        $response = new QtiString("hel\nlo");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("wor\nld");
        $this->assertFalse(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test that empty strings and null values are handled correctly
     */
    public function testIsResponseValidWithEmptyAndNull()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^$');

        $response = new QtiString("");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("\n\n");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $this->assertTrue(Utils::isResponseValid(null, $constraint));
    }

    /**
     * Test multiple container with newlines in values
     */
    public function testIsResponseValidWithMultipleContainer()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 3, '^.{3}$');

        $container = new MultipleContainer(
            BaseType::STRING,
            [
                new QtiString("ab\nc"),
                new QtiString("de\nf"),
                new QtiString("gh\ni")
            ]
        );

        $this->assertTrue(Utils::isResponseValid($container, $constraint));
    }

    /**
     * Test that non-string values are not affected by normalization
     */
    public function testIsResponseValidWithNonStringTypes()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1);

        $response = new QtiInteger(42);
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiFloat(3.14);
        $this->assertTrue(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test complex patterns with special characters and newlines
     */
    public function testIsResponseValidWithComplexPatterns()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^[a-z]+@[a-z]+\.[a-z]+$');

        $response = new QtiString("user\n@exam\nple.com");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("use\nr@");
        $this->assertFalse(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test that newlines in the middle of valid patterns work correctly
     */
    public function testIsResponseValidWithNewlinesInMiddleOfPattern()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 1, '^abc123$');

        $response = new QtiString("ab\nc1\n23");
        $this->assertTrue(Utils::isResponseValid($response, $constraint));

        $response = new QtiString("ab\nc1\n24");
        $this->assertFalse(Utils::isResponseValid($response, $constraint));
    }

    /**
     * Test multiple container with mixed valid and invalid values after normalization
     */
    public function testIsResponseValidWithMultipleContainerMixedValues()
    {
        $constraint = new ResponseValidityConstraint('test', 0, 3, '^.{2}$');

        $container = new MultipleContainer(
            BaseType::STRING,
            [
                new QtiString("a\nb"),
                new QtiString("c\nd"),
                new QtiString("e\nf\ng")
            ]
        );

        $this->assertFalse(Utils::isResponseValid($container, $constraint));
    }
}