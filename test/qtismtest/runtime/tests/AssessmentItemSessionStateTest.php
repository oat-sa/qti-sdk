<?php

declare(strict_types=1);

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentItemSessionState;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class AssessmentItemSessionStateTest
 */
class AssessmentItemSessionStateTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return AssessmentItemSessionState::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'initial',
            'interacting',
            'modalFeedback',
            'suspended',
            'closed',
            'notSelected',
            'solution',
            'review',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'INITIAL',
            'INTERACTING',
            'MODAL_FEEDBACK',
            'SUSPENDED',
            'CLOSED',
            'NOT_SELECTED',
            'SOLUTION',
            'REVIEW',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            AssessmentItemSessionState::INITIAL,
            AssessmentItemSessionState::INTERACTING,
            AssessmentItemSessionState::MODAL_FEEDBACK,
            AssessmentItemSessionState::SUSPENDED,
            AssessmentItemSessionState::CLOSED,
            AssessmentItemSessionState::NOT_SELECTED,
            AssessmentItemSessionState::SOLUTION,
            AssessmentItemSessionState::REVIEW,
        ];
    }
}
