<?php

namespace qtismtest\runtime\expressions;

use InvalidArgumentException;
use qtism\runtime\expressions\Utils;
use qtismtest\QtiSmTestCase;
use stdClass;

class ProcessorUtilsTest extends QtiSmTestCase
{
    /**
     * @dataProvider sanitizeVariableRefValidProvider
     */
    public function testSanitizeVariableRefValid($value, $expected)
    {
        $ref = $this->assertEquals(Utils::sanitizeVariableRef($value), $expected);
    }

    /**
     * @dataProvider sanitizeVariableRefInvalidProvider
     */
    public function testSanitizeVariableRefInvalid($value)
    {
        $this->expectException(InvalidArgumentException::class);
        $ref = Utils::sanitizeVariableRef($value);
    }

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
