<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentItemSessionState;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class AssessmentItemSessionStateTest
 *
 * @package qtismtest\runtime\tests
 */
class AssessmentItemSessionStateTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return AssessmentItemSessionState::class;
    }

    /**
     * @return array
     */
    protected function getNames()
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
    protected function getKeys()
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
    protected function getConstants()
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
