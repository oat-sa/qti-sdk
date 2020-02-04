<?php

namespace qtismtest\runtime\expressions;

use qtismtest\QtiSmTestCase;
use qtism\common\enums\BaseType;
use qtism\runtime\expressions\Utils;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OrderedContainer;

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
        return array(
            array('variableRef', 'variableRef'),
            array('{variableRef', 'variableRef'),
            array('variableRef}', 'variableRef'),
            array('{variableRef}', 'variableRef'),
            array('{{variableRef}}', 'variableRef'),
            array('', ''),
            array('{}', '')
        );
    }
    
    public function sanitizeVariableRefInvalidProvider()
    {
        return array(
            array(new \stdClass()),
            array(14),
            array(0),
            array(false)
        );
    }
}
