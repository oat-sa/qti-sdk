<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\TestResultsSubmission;
use qtismtest\QtiSmEnumTestCase;

class TestResultsSubmissionTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TestResultsSubmission::class;
    }

    protected function getNames()
    {
        return [
            'end',
            'outcomeProcessing',
        ];
    }

    protected function getKeys()
    {
        return [
            'END',
            'OUTCOME_PROCESSING',
        ];
    }

    protected function getConstants()
    {
        return [
            TestResultsSubmission::END,
            TestResultsSubmission::OUTCOME_PROCESSING,
        ];
    }
}
