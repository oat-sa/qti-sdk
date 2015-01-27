<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\ShowHide;
use qtism\data\content\ModalFeedbackCollection;
use qtism\data\content\ModalFeedback;
use qtism\data\AssessmentItem;

class AssessmentItemTest extends QtiSmTestCase {
    
	public function testModalFeedbackRules() {
	    $assessmentItem = new AssessmentItem('Q01', 'Question 1', false);
	    
	    $modalFeedback1 = new ModalFeedback('LOOKUP', 'SHOWME');
	    $modalFeedback2 = new ModalFeedback('LOOKUP2', 'HIDEME');
	    $modalFeedback2->setShowHide(ShowHide::HIDE);
	    $assessmentItem->setModalFeedbacks(new ModalFeedbackCollection(array($modalFeedback1, $modalFeedback2)));
	    
	    $modalFeedbackRules = $assessmentItem->getModalFeedbackRules();
	    $this->assertEquals(2, count($modalFeedbackRules));
	    
	    $this->assertEquals('LOOKUP', $modalFeedbackRules[0]->getOutcomeIdentifier());
	    $this->assertEquals('SHOWME', $modalFeedbackRules[0]->getIdentifier());
	    $this->assertEquals(ShowHide::SHOW, $modalFeedbackRules[0]->getShowHide());
	    
	    $this->assertEquals('LOOKUP2', $modalFeedbackRules[1]->getOutcomeIdentifier());
	    $this->assertEquals('HIDEME', $modalFeedbackRules[1]->getIdentifier());
	    $this->assertEquals(ShowHide::HIDE, $modalFeedbackRules[1]->getShowHide());
	}
}