<?php

require_once(dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\expressions\Utils;

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
        $this->setExpectedException('\\InvalidArgumentException');
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
