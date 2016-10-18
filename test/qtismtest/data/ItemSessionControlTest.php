<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\ItemSessionControl;

class ItemSessionControlTest extends QtiSmTestCase {
	
    public function testIsDefault() {
        $itemSessionControl = new ItemSessionControl();
        $this->assertTrue($itemSessionControl->isDefault());
        
        $itemSessionControl->setMaxAttempts(0);
        $this->assertFalse($itemSessionControl->isDefault());
    }
    
    public function testSetMaxAttemptsWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "MaxAttempts must be an integer, 'boolean' given."
        );
        
        $itemSessionControl->setMaxAttempts(true);
    }
    
    public function testSetShowFeedbackWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "ShowFeedback must be a boolean, 'integer' given."
        );
        
        $itemSessionControl->setShowFeedback(999);
    }
    
    public function testSetAllowReviewWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "AllowReview must be a boolean, 'integer' given."
        );
        
        $itemSessionControl->setAllowReview(999);
    }
    
    public function testSetShowSolutionWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "ShowSolution must be a boolean, 'integer' given."
        );
        
        $itemSessionControl->setShowSolution(999);
    }
    
    public function testSetAllowCommentWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "AllowComment must be a boolean, 'integer' given."
        );
        
        $itemSessionControl->setAllowComment(999);
    }
    
    public function testSetAllowSkippingWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "AllowSkipping must be a boolean, 'integer' given."
        );
        
        $itemSessionControl->setAllowSkipping(999);
    }
    
    public function testSetValidateResponsesWrongType() {
        $itemSessionControl = new ItemSessionControl();
        
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "ValidateResponses must be a boolean value, 'integer' given."
        );
        
        $itemSessionControl->setValidateResponses(999);
    }
}
