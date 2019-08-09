<?php

namespace qtismtest\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\results\AssessmentResult;
use qtism\data\results\TestResult;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\results\AssessmentResultBuilder;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

class AssessmentResultBuilderTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testBasic()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/linear_5_items.xml');
        $session->beginTestSession();

        $session->beginAttempt();
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('ChoiceA')
                )
            ])
        );

        $assessmentResultBuilder = new AssessmentResultBuilder($session);
        $assessmentResult = $assessmentResultBuilder->buildResult();

        $this->assertInstanceOf(AssessmentResult::class, $assessmentResult);

        $testResult = $assessmentResult->getTestResult();
        $this->assertInstanceOf(TestResult::class, $testResult);
        $this->assertCount(0, $testResult->getItemVariables());

        $itemResults = $assessmentResult->getItemResults();
        $this->assertCount(5, $itemResults);
    }
}