<?php

namespace qtismtest\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\ItemResult;
use qtism\data\results\ResultOutcomeVariable;
use qtism\data\results\ResultResponseVariable;
use qtism\data\results\SessionStatus;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\results\ItemResultBuilder;
use qtismtest\QtiSmAssessmentItemTestCase;

class ItemResultBuilderTest extends QtiSmAssessmentItemTestCase
{
    public function testBasic()
    {
        $itemSession = self::instantiateBasicAssessmentItemSession();
        $itemSession->beginAttempt();
        $itemSession->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('ChoiceB')
                )
            ])
        );

        $itemResultBuilder = new ItemResultBuilder($itemSession);
        $itemResult = $itemResultBuilder->buildResult();

        $this->assertInstanceOf(ItemResult::class, $itemResult);
        $this->assertEquals('Q01', $itemResult->getIdentifier());
        $this->assertInstanceOf(\DateTime::class, $itemResult->getDatestamp());
        $this->assertEquals(SessionStatus::STATUS_FINAL, $itemResult->getSessionStatus());

        $variables = $itemResult->getItemVariables();
        $this->assertCount(5, $variables);

        $this->assertInstanceOf(ResultResponseVariable::class, $variables[0]);
        $this->assertEquals('numAttempts', $variables[0]->getIdentifier());

        $this->assertInstanceOf(ResultResponseVariable::class, $variables[1]);
        $this->assertEquals('duration', $variables[1]->getIdentifier());

        $this->assertInstanceOf(ResultOutcomeVariable::class, $variables[2]);
        $this->assertEquals('completionStatus', $variables[2]->getIdentifier());

        $this->assertInstanceOf(ResultOutcomeVariable::class, $variables[3]);
        $this->assertEquals('SCORE', $variables[3]->getIdentifier());

        $this->assertInstanceOf(ResultResponseVariable::class, $variables[4]);
        $this->assertEquals('RESPONSE', $variables[4]->getIdentifier());
    }
}