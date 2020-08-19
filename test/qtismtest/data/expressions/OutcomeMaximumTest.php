<?php

namespace qtismtest\data\expressions;

use qtism\data\expressions\OutcomeMaximum;
use qtismtest\QtiSmTestCase;

class OutcomeMaximumTest extends QtiSmTestCase
{
    public function testOutcomeMaximum()
    {
        $outcomeMaximum = new OutcomeMaximum('SCORE', 'WEIGHT');
        $this->assertInstanceOf(OutcomeMaximum::class, $outcomeMaximum);
        $this->assertEquals('SCORE', $outcomeMaximum->getOutcomeIdentifier());
        $this->assertEquals('WEIGHT', $outcomeMaximum->getWeightIdentifier());

        $this->assertEquals([], $outcomeMaximum->getIncludeCategories()->getArrayCopy());
        $this->assertEquals([], $outcomeMaximum->getExcludeCategories()->getArrayCopy());
    }
}
