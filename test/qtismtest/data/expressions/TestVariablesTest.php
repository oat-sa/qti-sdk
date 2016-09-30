<?php
namespace qtismtest\data\expressions;

use qtismtest\QtiSmTestCase;
use qtism\data\expressions\TestVariables;
use qtism\common\enums\BaseType;

class TestVariablesTest extends QtiSmTestCase
{
    public function testTestVariables()
    {
        $testVariables = new TestVariables('SCORE', BaseType::FLOAT, 'WEIGHT');
        $this->assertInstanceOf('qtism\\data\\expressions\\TestVariables', $testVariables);
        $this->assertEquals('SCORE', $testVariables->getVariableIdentifier());
        $this->assertEquals('WEIGHT', $testVariables->getWeightIdentifier());
        $this->assertEquals(BaseType::FLOAT, $testVariables->getBaseType());
        
        $this->assertEquals(array(), $testVariables->getIncludeCategories()->getArrayCopy());
        $this->assertEquals(array(), $testVariables->getExcludeCategories()->getArrayCopy());
    }
}
