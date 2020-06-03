<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentTestSessionState;
use qtismtest\QtiSmEnumTestCase;

class AssessmentTestSessionStateTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return AssessmentTestSessionState::class;
    }

    protected function getNames()
    {
        return [
            'initial',
            'interacting',
            'modalFeedback',
            'suspended',
            'closed',
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
        ];
    }

    protected function getConstants()
    {
        return [
            AssessmentTestSessionState::INITIAL,
            AssessmentTestSessionState::INTERACTING,
            AssessmentTestSessionState::MODAL_FEEDBACK,
            AssessmentTestSessionState::SUSPENDED,
            AssessmentTestSessionState::CLOSED,
        ];
    }
}
