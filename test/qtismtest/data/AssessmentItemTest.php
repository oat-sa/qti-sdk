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
                self::samplesDir() . 'custom/items/response_constraints/choice_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/choice_min.xml',
                array(
                    array('RESPONSE', 2, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/choice_default.xml',
                array(
                    array('RESPONSE', 0, 0, '')
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
                self::samplesDir() . 'custom/items/response_constraints/associate_min_max.xml',
                array(
                    array('RESPONSE', 2, 3, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/associate_min.xml',
                array(
                    // maxConstraint is 1 because by default, maxAssociations = 1 in associateInteraction.
                    array('RESPONSE', 1, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/associate_default.xml',
                array(
                    // maxConstraint is 1 because by default, maxAssociations = 1 in associateInteraction.
                    array('RESPONSE', 0, 1, '')
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
                // Default for minStrings/maxStrings.
                self::samplesDir() . 'ims/items/2_2/extended_text.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_min.xml',
                array(
                    array('RESPONSE', 1, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_max.xml',
                array(
                    array('RESPONSE', 0, 2, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_patternmask.xml',
                array(
                    array('RESPONSE', 0, 0, '[\S]{10,15}')
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
                    array('RESPONSE27', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackblock_solution_random.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackblock_templateblock.xml',
                array(
                    array('RESPONSE1', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackInline.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/gap_match.xml',
                array()
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/graphic_associate.xml',
                array(
                    array('RESPONSE', 0, 3, '')
                )
            ),
            array(
                // maxConstraint is 1 because by default, maxAssociations = 1 in graphicAssociateInteraction.
                self::samplesDir() . 'custom/items/response_constraints/graphic_associate_min.xml',
                array(
                    array('RESPONSE', 1, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/graphic_associate_min_max.xml',
                array(
                    array('RESPONSE', 1, 3, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/graphic_gap_match.xml',
                array()
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/hotspot.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/hotspot_default.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/hotspot_min.xml',
                array(
                    array('RESPONSE', 2, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/hotspot_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/hottext.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/hottext_default.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/hottext_min.xml',
                array(
                    array('RESPONSE', 2, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/hottext_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/inline_choice.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/inline_choice_required.xml',
                array(
                    array('RESPONSE', 1, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/likert.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/match.xml',
                array(
                    array('RESPONSE', 0, 4, '')
                )
            ),
            array(
                // maxAssociations = 1 because default for matchInteraction is 1.
                self::samplesDir() . 'custom/items/response_constraints/match_default.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                // maxAssociations = 1 because default for matchInteraction is 1.
                self::samplesDir() . 'custom/items/response_constraints/match_min.xml',
                array(
                    array('RESPONSE', 1, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'custom/items/response_constraints/match_min_max.xml',
                array(
                    array('RESPONSE', 2, 3, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/math.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/mc_calc3.xml',
                array(
                    array('RESPONSE0', 1, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/mc_stat2.xml',
                array(
                    array('RESPONSE0', 0, 1, ''),
                    array('RESPONSE1', 0, 1, ''),
                    array('RESPONSE2', 0, 1, ''),
                    array('RESPONSE3', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/modalFeedback.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/multi-input.xml',
                array(
                    array('RESPONSE1', 0, 1, ''),
                    array('RESPONSE2', 0, 1, ''),
                    array('RESPONSE3', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/nested_object.xml',
                array(
                    array('RESPONSE', 0, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/order.xml',
                array(
                    array('RESPONSE', 3, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/order_rtl.xml',
                array(
                    array('RESPONSE', 3, 0, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/orkney1.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/orkney2.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/position_object.xml',
                array(
                    array('RESPONSE', 0, 3, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/slider.xml',
                array()
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/template.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/text_entry.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
            array(
                self::samplesDir() . 'ims/items/2_2/select_point.xml',
                array(
                    array('RESPONSE', 0, 1, '')
                )
            ),
        );
    }
}
