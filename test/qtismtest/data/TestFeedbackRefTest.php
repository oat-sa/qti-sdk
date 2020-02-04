<?php

namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\TestFeedbackRef;
use qtism\data\TestFeedbackAccess;
use qtism\data\ShowHide;

class TestFeedbackRefTest extends QtiSmTestCase
{
    public function testSetAccessWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'1' is not a value from the TestFeedbackAccess enumeration."
        );
        
        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', true, ShowHide::SHOW, 'ref.xml');
    }
    
    public function testSetShowHideWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'1' is not a value from the ShowHide enumeration."
        );
        
        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, true, 'ref.xml');
    }
    
    public function testSetOutcomeIdentifierWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );
        
        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 999, TestFeedbackAccess::DURING, ShowHide::SHOW, 'ref.xml');
    }
    
    public function testSetIdentifierWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );
        
        $testFeedbackRef = new TestFeedbackRef(999, 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, ShowHide::SHOW, 'ref.xml');
    }
    
    public function testSetHrefWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid URI."
        );
        
        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, ShowHide::SHOW, 999);
    }
}
