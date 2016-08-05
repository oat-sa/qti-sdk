<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmEnumTestCase;
use qtism\runtime\tests\AssessmentTestSessionState;

class AssessmentTestSessionStateTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return AssessmentTestSessionState::class;
    }
    
    protected function getNames()
    {
        return array(
            'initial',
            'interacting',
            'modalFeedback',
            'suspended',
            'closed'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'INITIAL',
            'INTERACTING',
            'MODAL_FEEDBACK',
            'SUSPENDED',
            'CLOSED'
        );
    }
    
    protected function getConstants()
    {
        return array(
            AssessmentTestSessionState::INITIAL,
            AssessmentTestSessionState::INTERACTING,
            AssessmentTestSessionState::MODAL_FEEDBACK,
            AssessmentTestSessionState::SUSPENDED,
            AssessmentTestSessionState::CLOSED
        );
    }
}
