<?php

namespace qtismtest\runtime\expressions;

use InvalidArgumentException;
use qtism\runtime\expressions\Utils;
use qtismtest\QtiSmTestCase;
use stdClass;

/**
 * Class ProcessorUtilsTest
 */
class ProcessorUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider sanitizeVariableRefValidProvider
     * @param string $value
     * @param string $expected
     */
    public function testSanitizeVariableRefValid($value, $expected)
    {
        $ref = $this->assertEquals(Utils::sanitizeVariableRef($value), $expected);
    }

    /**
     * @dataProvider sanitizeVariableRefInvalidProvider
     * @param mixed $value
     */
    public function testSanitizeVariableRefInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $ref = Utils::sanitizeVariableRef($value);
    }

    /**
     * @return array
     */
    public function sanitizeVariableRefValidProvider()
    {
        return [
            ['variableRef', 'variableRef'],
            ['{variableRef', 'variableRef'],
            ['variableRef}', 'variableRef'],
            ['{variableRef}', 'variableRef'],
            ['{{variableRef}}', 'variableRef'],
            ['', ''],
            ['{}', ''],
        ];
    }

    /**
     * @return array
     */
    public function sanitizeVariableRefInvalidProvider()
    {
        return [
            [new stdClass()],
            [14],
            [0],
            [false],
        ];
    }
}
