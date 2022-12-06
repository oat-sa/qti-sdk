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
    public function testSanitizeVariableRefValid($value, $expected): void
    {
        $ref = $this::assertEquals(Utils::sanitizeVariableRef($value), $expected);
    }

    /**
     * @dataProvider sanitizeVariableRefInvalidProvider
     * @param mixed $value
     */
    public function testSanitizeVariableRefInvalid($value): void
    {
        $this->expectException(InvalidArgumentException::class);
        $ref = Utils::sanitizeVariableRef($value);
    }

    /**
     * @return array
     */
    public function sanitizeVariableRefValidProvider(): array
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
    public function sanitizeVariableRefInvalidProvider(): array
    {
        return [
            [new stdClass()],
            [14],
            [0],
            [false],
        ];
    }
}
