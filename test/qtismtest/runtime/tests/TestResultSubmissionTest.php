<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\TestResultsSubmission;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class TestResultsSubmissionTest
 *
 * @package qtismtest\runtime\tests
 */
class TestResultsSubmissionTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return TestResultsSubmission::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'end',
            'outcomeProcessing',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'END',
            'OUTCOME_PROCESSING',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            TestResultsSubmission::END,
            TestResultsSubmission::OUTCOME_PROCESSING,
        ];
    }
}
