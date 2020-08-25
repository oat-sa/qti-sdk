<?php

namespace qtismtest\runtime\tests\mocks;

use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentTestSession;

/**
 * Class SimpleResultsSubmittableTestSession
 *
 * @package qtismtest\runtime\tests\mocks
 */
class SimpleResultsSubmittableTestSession extends AssessmentTestSession
{
    private $submittedTestResults = [];

    private $submittedItemResults = [];

    protected function submitTestResults()
    {
        foreach ($this as $id => $var) {
            $this->addTestResult($id, $var->getValue());
        }
    }

    /**
     * @param AssessmentItemSession $assessmentItemSession
     * @param int $occurence
     */
    protected function submitItemResults(AssessmentItemSession $assessmentItemSession, $occurence = 0)
    {
        foreach ($assessmentItemSession as $id => $var) {
            $this->addItemResultResult($assessmentItemSession->getAssessmentItem()->getIdentifier() . '.' . $occurence . '.' . $id, $var->getValue());
        }
    }

    /**
     * @param $identifier
     * @param $value
     */
    protected function addTestResult($identifier, $value)
    {
        if (isset($this->submittedTestResults[$identifier]) === false) {
            $this->submittedTestResults[$identifier] = [];
        }

        $this->submittedTestResults[$identifier][] = $value;
    }

    /**
     * @param $identifier
     * @param $value
     */
    protected function addItemResultResult($identifier, $value)
    {
        if (isset($this->submittedItemResults[$identifier]) === false) {
            $this->submittedItemResults[$identifier] = [];
        }

        $this->submittedItemResults[$identifier][] = $value;
    }

    /**
     * @return array
     */
    public function getSubmittedTestResults()
    {
        return $this->submittedTestResults;
    }

    /**
     * @return array
     */
    public function getSubmittedItemResults()
    {
        return $this->submittedItemResults;
    }
}
