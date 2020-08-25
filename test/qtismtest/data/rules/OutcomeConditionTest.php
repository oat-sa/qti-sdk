<?php

namespace qtismtest\data\state;

use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\expressions\ExpressionCollection;
use qtism\data\expressions\operators\Match;
use qtism\data\rules\OutcomeCondition;
use qtism\data\rules\OutcomeElse;
use qtism\data\rules\OutcomeIf;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtismtest\QtiSmTestCase;

/**
 * Class OutcomeConditionTest
 *
 * @package qtismtest\data\state
 */
class OutcomeConditionTest extends QtiSmTestCase
{
    public function testHasOutcomeElseGetComponents()
    {
        $outcomeIf = new OutcomeIf(
            new Match(
                new ExpressionCollection(
                    [
                        new BaseValue(BaseType::BOOLEAN, true),
                        new BaseValue(BaseType::BOOLEAN, true),
                    ]
                )
            ),
            new OutcomeRuleCollection(
                [
                    new SetOutcomeValue(
                        'OUTCOME',
                        new BaseValue(BaseType::BOOLEAN, true)
                    ),
                ]
            )
        );

        $outcomeElse = new OutcomeElse(
            new OutcomeRuleCollection(
                [
                    new SetOutcomeValue(
                        'OUTCOME',
                        new BaseValue(BaseType::BOOLEAN, false)
                    ),
                ]
            )
        );

        $outcomeCondition = new OutcomeCondition($outcomeIf, null, $outcomeElse);
        $this->assertTrue($outcomeCondition->hasOutcomeElse());

        $components = $outcomeCondition->getComponents();
        $this->assertSame($outcomeIf, $components[0]);
        $this->assertSame($outcomeElse, $components[1]);
    }
}
