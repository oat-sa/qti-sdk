<?php

declare(strict_types=1);

namespace qtismtest\data\expressions;

use qtism\data\expressions\OutcomeMinimum;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeMinimumTest
 */
class OutcomeMinimumTest extends QtiSmTestCase
{
    public function testOutcomeMaximum(): void
    {
        $outcomeMinimum = new OutcomeMinimum('SCORE', 'WEIGHT');
        $this::assertInstanceOf(OutcomeMinimum::class, $outcomeMinimum);
        $this::assertEquals('SCORE', $outcomeMinimum->getOutcomeIdentifier());
        $this::assertEquals('WEIGHT', $outcomeMinimum->getWeightIdentifier());

        $this::assertEquals([], $outcomeMinimum->getIncludeCategories()->getArrayCopy());
        $this::assertEquals([], $outcomeMinimum->getExcludeCategories()->getArrayCopy());
    }
}
