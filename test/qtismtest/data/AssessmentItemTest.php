<?php
namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\ShowHide;
use qtism\data\content\ModalFeedbackCollection;
use qtism\data\content\ModalFeedback;
use qtism\data\AssessmentItem;
use qtism\data\storage\xml\XmlDocument;

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
    
    /**
     * @dataProvider getResponseValidityConstraintsProvider
     */
    public function testGetResponseValidityConstraints($path, array $expected) {
        $doc = new XmlDocument();
        $doc->load($path);
        
        $assessmentItem = $doc->getDocumentComponent();
        $responseValidityConstraints = $assessmentItem->getResponseValidityConstraints();
        
        $this->assertEquals(count($expected), count($responseValidityConstraints));
        
        for ($i = 0; $i < count($responseValidityConstraints); $i++) {
            $this->assertEquals($expected[$i][0], $responseValidityConstraints[$i]->getResponseIdentifier());
            $this->assertEquals($expected[$i][1], $responseValidityConstraints[$i]->getMinConstraint(), 'minConstraint failed for ' . $expected[$i][0]);
            $this->assertEquals($expected[$i][2], $responseValidityConstraints[$i]->getMaxConstraint(), 'maxConstraint failed for ' . $expected[$i][0]);
            $this->assertEquals($expected[$i][3], $responseValidityConstraints[$i]->getPatternMask());
        }
    }
    
    public function getResponseValidityConstraintsProvider() {
        
        return array(
            array(
                self::samplesDir() . 'ims/items/2_2/choice.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/adaptive.xml',
                array(
                    array('DOOR', 0, 1, ''),
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/adaptive_template.xml',
                array(
                    array('DOOR', 0, 1, ''),
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/associate.xml',
                array(
                    array('RESPONSE', 0, 3, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/choice_fixed.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple_rtl.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/extended_text.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/extended_text_rubric.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackblock_adaptive.xml',
                array(
                    array('RESPONSE1', 0, 1, ''),
                    array('RESPONSE21', 0, 1, ''),
                    array('RESPONSE22', 0, 1, ''),
                    array('RESPONSE23', 0, 1, ''),
                    array('RESPONSE24', 0, 1, ''),
                    array('RESPONSE25', 0, 1, ''),
                    array('RESPONSE26', 0, 1, ''),
                    array('RESPONSE27', 0, 1, ''),
                )
            ),
        );
    }
}
