<?php

namespace qtismtest\runtime\tests\mocks;

use qtism\data\AssessmentTest;
use qtism\data\IAssessmentItem;
use qtism\runtime\tests\AbstractSessionManager;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\Route;

/**
 * Class SimpleResultsSubmittableTestSessionFactory
 */
class SimpleResultsSubmittableTestSessionFactory extends AbstractSessionManager
{
    /**
     * @param AssessmentTest $test
     * @param Route $route
     * @return AssessmentTestSession|SimpleResultsSubmittableTestSession
     */
    protected function instantiateAssessmentTestSession(AssessmentTest $test, Route $route)
    {
        return new SimpleResultsSubmittableTestSession($test, $this, $route);
    }

    /**
     * @param IAssessmentItem $assessmentItem
     * @param int $navigationMode
     * @param int $submissionMode
     * @return AssessmentItemSession
     */
    protected function instantiateAssessmentItemSession(IAssessmentItem $assessmentItem, $navigationMode, $submissionMode)
    {
        return new AssessmentItemSession($assessmentItem, $this, $navigationMode, $submissionMode);
    }
}
