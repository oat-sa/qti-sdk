<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\AssessmentItemRef;

class AssessmentItemRefTest extends QtiSmTestCase
{
	public function testCreateAssessmentItemRefWrongIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );
        
        $assessmentItemRef = new AssessmentItemRef('999', 'Nine Nine Nine');
    }
    
    public function testSetRequiredWrongType()
    {
        $assessmentItemRef = new AssessmentItemRef('nine', 'Nine Nine Nine');
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Required must be a boolean, 'string' given."
        );
        
        $assessmentItemRef->setRequired('test');
    }
    
    public function testSetFixedWrongType()
    {
        $assessmentItemRef = new AssessmentItemRef('nine', 'Nine Nine Nine');
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Fixed must be a boolean, 'string' given."
        );
        
        $assessmentItemRef->setFixed('test');
    }
}
