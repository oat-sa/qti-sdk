<?php

namespace qtismtest\data\state;

use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\OutcomeElse;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeElseTest
 *
 * @package qtismtest\data\state
 */
class OutcomeElseTest extends QtiSmTestCase
{
    public function testGetComponents()
    {
        $outcomeElse = new OutcomeElse(
            new OutcomeRuleCollection(
                [
                    new SetOutcomeValue(
                        'SCORE',
                        new BaseValue(BaseType::BOOLEAN, true)
                    ),
                ]
            )
        );

        $components = $outcomeElse->getComponents();
        $this->assertCount(1, $components);
        $this->assertInstanceOf(SetOutcomeValue::class, $components[0]);
    }
}
