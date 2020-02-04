<?php

namespace qtismtest\data\expressions;

use qtismtest\QtiSmTestCase;
use qtism\data\expressions\OutcomeMaximum;
use qtism\common\enums\BaseType;

class OutcomeMaximumTest extends QtiSmTestCase
{
    public function testOutcomeMaximum()
    {
        $outcomeMaximum = new OutcomeMaximum('SCORE', 'WEIGHT');
        $this->assertInstanceOf('qtism\\data\\expressions\\OutcomeMaximum', $outcomeMaximum);
        $this->assertEquals('SCORE', $outcomeMaximum->getOutcomeIdentifier());
        $this->assertEquals('WEIGHT', $outcomeMaximum->getWeightIdentifier());
        
        $this->assertEquals(array(), $outcomeMaximum->getIncludeCategories()->getArrayCopy());
        $this->assertEquals(array(), $outcomeMaximum->getExcludeCategories()->getArrayCopy());
    }
}
