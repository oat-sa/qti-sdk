<?php

namespace qtismtest\data\state;

use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtismtest\QtiSmTestCase;
use qtism\data\rules\OutcomeElse;

class OutcomeElseTest extends QtiSmTestCase
{
    public function testGetComponents()
    {
        $outcomeElse = new OutcomeElse(
            new OutcomeRuleCollection(
                array(
                    new SetOutcomeValue(
                        'SCORE',
                        new BaseValue(BaseType::BOOLEAN, true)
                    )
                )
            )
        );
        
        $components = $outcomeElse->getComponents();
        $this->assertCount(1, $components);
        $this->assertInstanceOf('qtism\\data\\rules\\SetOutcomeValue', $components[0]);
    }
}
