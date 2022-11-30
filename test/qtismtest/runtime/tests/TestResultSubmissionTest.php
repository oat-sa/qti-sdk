<?php

declare(strict_types=1);

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\TestResultsSubmission;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class TestResultsSubmissionTest
 */
class TestResultsSubmissionTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return TestResultsSubmission::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'end',
            'outcomeProcessing',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'END',
            'OUTCOME_PROCESSING',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            TestResultsSubmission::END,
            TestResultsSubmission::OUTCOME_PROCESSING,
        ];
    }
}
