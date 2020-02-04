<?php

namespace qtismtest\data\expressions;

use qtismtest\QtiSmTestCase;
use qtism\data\expressions\OutcomeMinimum;
use qtism\common\enums\BaseType;

class OutcomeMinimumTest extends QtiSmTestCase
{
    public function testOutcomeMaximum()
    {
        $outcomeMinimum = new OutcomeMinimum('SCORE', 'WEIGHT');
        $this->assertInstanceOf('qtism\\data\\expressions\\OutcomeMinimum', $outcomeMinimum);
        $this->assertEquals('SCORE', $outcomeMinimum->getOutcomeIdentifier());
        $this->assertEquals('WEIGHT', $outcomeMinimum->getWeightIdentifier());
        
        $this->assertEquals(array(), $outcomeMinimum->getIncludeCategories()->getArrayCopy());
        $this->assertEquals(array(), $outcomeMinimum->getExcludeCategories()->getArrayCopy());
    }
}
