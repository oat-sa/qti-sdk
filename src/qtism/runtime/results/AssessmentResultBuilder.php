<?php

namespace qtism\runtime\results;

use qtism\common\datatypes\QtiIdentifier;
use qtism\data\AssessmentItemRef;
use qtism\data\results\AssessmentResult;
use qtism\data\results\Context;
use qtism\data\results\ItemResultCollection;
use qtism\data\results\TestResult;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentTestSession;

class AssessmentResultBuilder extends AbstractResultBuilder
{

    public function buildResult()
    {
        /** @var AssessmentTestSession $state */
        $state = $this->state;

        $assessmentContext = new Context();
        $assessmentResult = new AssessmentResult($assessmentContext);

        $testResult = new TestResult(
            new QtiIdentifier($state->getSessionId()),
            new \DateTime()
        );

        $testResult->setItemVariables($this->buildVariables());
        $assessmentResult->setTestResult($testResult);

        $itemResults = new ItemResultCollection();

        /** @var AssessmentItemRef $assessmentItemRef */
        foreach ($state->getRoute()->getAssessmentItemRefs() as $assessmentItemRef) {
            $assessmentItemSessions = $state->getAssessmentItemSessions($assessmentItemRef->getIdentifier());

            /** @var AssessmentItemSession $assessmentItemSession */
            foreach ($assessmentItemSessions as $assessmentItemSession) {
                $itemResultBuilder = new ItemResultBuilder($assessmentItemSession);
                $itemResults[] = $itemResultBuilder->buildResult();
            }
        }

        $assessmentResult->setItemResults($itemResults);

        return $assessmentResult;
    }

    protected function getAllVariables()
    {
        return $this->state->getAllVariables();
    }
}