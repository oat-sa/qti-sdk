<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentTestSessionState;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class AssessmentTestSessionStateTest
 */
class AssessmentTestSessionStateTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return AssessmentTestSessionState::class;
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
        ];
    }

    /**
     * @return array
     */
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
