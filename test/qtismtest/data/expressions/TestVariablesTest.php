<?php

namespace qtismtest\data\expressions;

use qtism\common\enums\BaseType;
use qtism\data\expressions\TestVariables;
use qtismtest\QtiSmTestCase;

/**
 * Class TestVariablesTest
 *
 * @package qtismtest\data\expressions
 */
class TestVariablesTest extends QtiSmTestCase
{
    public function testTestVariables()
    {
        $testVariables = new TestVariables('SCORE', BaseType::FLOAT, 'WEIGHT');
        $this->assertInstanceOf(TestVariables::class, $testVariables);
        $this->assertEquals('SCORE', $testVariables->getVariableIdentifier());
        $this->assertEquals('WEIGHT', $testVariables->getWeightIdentifier());
        $this->assertEquals(BaseType::FLOAT, $testVariables->getBaseType());

        $this->assertEquals([], $testVariables->getIncludeCategories()->getArrayCopy());
        $this->assertEquals([], $testVariables->getExcludeCategories()->getArrayCopy());
    }
}
