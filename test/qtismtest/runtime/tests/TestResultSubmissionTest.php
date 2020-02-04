<?php

namespace qtismtest\runtime\tests;

use qtismtest\QtiSmEnumTestCase;
use qtism\runtime\tests\TestResultsSubmission;

class TestResultsSubmissionTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TestResultsSubmission::class;
    }
    
    protected function getNames()
    {
        return array(
            'end',
            'outcomeProcessing'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'END',
            'OUTCOME_PROCESSING'
        );
    }
    
    protected function getConstants()
    {
        return array(
            TestResultsSubmission::END,
            TestResultsSubmission::OUTCOME_PROCESSING
        );
    }
}
