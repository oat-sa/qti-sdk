<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentItemSessionState;
use qtismtest\QtiSmEnumTestCase;

class AssessmentItemSessionStateTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return AssessmentItemSessionState::class;
    }

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
