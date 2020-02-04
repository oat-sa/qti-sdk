<?php

namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\TestFeedback;
use qtism\data\content\FlowStaticCollection;

class TestFeedbackTest extends QtiSmTestCase
{
    public function testSetAccessWrongType()
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'1' is not a value from the TestFeedbackAccess enumeration."
        );
        
        $testFeedback->setAccess(true);
    }
    
    public function testSetOutcomeIdentifierWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );
        
        $testFeedback = new TestFeedback('IDENTIFIER', 999, new FlowStaticCollection());
    }
    
    public function testSetShowHideWrongType()
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'1' is not a value from the ShowHide enumeration."
        );
        
        $testFeedback->setShowHide(true);
    }
    
    public function testSetIdentifierWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );
        
        $testFeedback = new TestFeedback(999, 'OUTCOMEIDENTIFIER', new FlowStaticCollection());
    }
    
    public function testSetTitleWrongType()
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Title must be a string, 'integer' given."
        );
        
        $testFeedback->setTitle(999);
    }
}
