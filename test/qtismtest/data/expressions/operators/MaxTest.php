<?php

namespace qtismtest\data\expressions\operators;

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Max;
use qtismtest\QtiSmTestCase;

/**
 * Class MaxTest
 */
class MaxTest extends QtiSmTestCase
{
    public function testInstantiation()
    {
        $expressions = new ExpressionCollection();
        $expressions[] = new BaseValue(BaseType::INTEGER, 15);
        $expressions[] = new BaseValue(BaseType::INTEGER, 16);
        $max = new Max($expressions);

        $this->assertInstanceOf(Max::class, $max);
        $this->assertTrue(in_array(Cardinality::SINGLE, $max->getAcceptedCardinalities()));
        $this->assertTrue(in_array(Cardinality::MULTIPLE, $max->getAcceptedCardinalities()));
        $this->assertTrue(in_array(Cardinality::ORDERED, $max->getAcceptedCardinalities()));
        $this->assertTrue(in_array(BaseType::INTEGER, $max->getAcceptedBaseTypes()));
        $this->assertTrue(in_array(BaseType::FLOAT, $max->getAcceptedBaseTypes()));
        $this->assertEquals(1, $max->getMinOperands());
        $this->assertEquals(-1, $max->getMaxOperands());
    }
}
