<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmEnumTestCase;
use qtism\runtime\tests\AssessmentItemSessionState;

class AssessmentItemSessionStateTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return AssessmentItemSessionState::class;
    }
    
    protected function getNames()
    {
        return array(
            'initial',
            'interacting',
            'modalFeedback',
            'suspended',
            'closed',
            'notSelected',
            'solution',
            'review'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'INITIAL',
            'INTERACTING',
            'MODAL_FEEDBACK',
            'SUSPENDED',
            'CLOSED',
            'NOT_SELECTED',
            'SOLUTION',
            'REVIEW'
        );
    }
    
    protected function getConstants()
    {
        return array(
            AssessmentItemSessionState::INITIAL,
            AssessmentItemSessionState::INTERACTING,
            AssessmentItemSessionState::MODAL_FEEDBACK,
            AssessmentItemSessionState::SUSPENDED,
            AssessmentItemSessionState::CLOSED,
            AssessmentItemSessionState::NOT_SELECTED,
            AssessmentItemSessionState::SOLUTION,
            AssessmentItemSessionState::REVIEW
        );
    }
}
