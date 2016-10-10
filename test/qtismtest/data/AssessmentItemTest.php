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
            
            if (isset($expected[$i][4]) === true) {
                // Let's check association constraints.
                $expectedAssociationValidityConstraints = $expected[$i][4];
                $associationValidityConstraints = $responseValidityConstraints[$i]->getAssociationValidityConstraints();
                
                $this->assertEquals(count($expectedAssociationValidityConstraints), count($associationValidityConstraints));
                
                for ($j = 0; $j < count($associationValidityConstraints); $j++) {
                    $this->assertEquals($expectedAssociationValidityConstraints[$j][0], $associationValidityConstraints[$j]->getIdentifier());
                    $this->assertEquals($expectedAssociationValidityConstraints[$j][1], $associationValidityConstraints[$j]->getMinConstraint());
                    $this->assertEquals($expectedAssociationValidityConstraints[$j][2], $associationValidityConstraints[$j]->getMaxConstraint());
                }
            }
        }
    }
    
    public function getResponseValidityConstraintsProvider() {
        
        return array(
            // # 0
            array(
                self::samplesDir() . 'ims/items/2_2/choice.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 1
            array(
                self::samplesDir() . 'custom/items/response_constraints/choice_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '', array())
                )
            ),
            // # 2
            array(
                self::samplesDir() . 'custom/items/response_constraints/choice_min.xml',
                array(
                    array('RESPONSE', 2, 0, '', array())
                )
            ),
            // # 3
            array(
                self::samplesDir() . 'custom/items/response_constraints/choice_default.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 4
            array(
                self::samplesDir() . 'ims/items/2_2/adaptive.xml',
                array(
                    array('DOOR', 0, 1, '', array()),
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 5
            array(
                self::samplesDir() . 'ims/items/2_2/adaptive_template.xml',
                array(
                    array('DOOR', 0, 1, '', array()),
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 6
            array(
                self::samplesDir() . 'ims/items/2_2/associate.xml',
                array(
                    array('RESPONSE', 0, 3, '', array(
                        array('A', 0, 1),
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('M', 0, 1),
                        array('P', 0, 1),
                    ))
                )
            ),
            // # 7
            array(
                self::samplesDir() . 'custom/items/response_constraints/associate_min_max.xml',
                array(
                    array('RESPONSE', 2, 3, '', array(
                        array('A', 0, 1),
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('M', 0, 1),
                        array('P', 0, 1),
                    ))
                )
            ),
            // # 8
            array(
                self::samplesDir() . 'custom/items/response_constraints/associate_min.xml',
                array(
                    // maxConstraint is 1 because by default, maxAssociations = 1 in associateInteraction.
                    array('RESPONSE', 1, 1, '', array(
                        array('A', 0, 1),
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('M', 0, 1),
                        array('P', 0, 1),
                    ))
                )
            ),
            // # 9
            array(
                self::samplesDir() . 'custom/items/response_constraints/associate_default.xml',
                array(
                    // maxConstraint is 1 because by default, maxAssociations = 1 in associateInteraction.
                    array('RESPONSE', 0, 1, '', array(
                        array('A', 0, 1),
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('M', 0, 1),
                        array('P', 0, 1),
                    ))
                )
            ),
            // # 10
            array(
                self::samplesDir() . 'ims/items/2_2/choice_fixed.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 11
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 12
            array(
                self::samplesDir() . 'ims/items/2_2/choice_multiple_rtl.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 13
            array(
                // Default for minStrings/maxStrings.
                self::samplesDir() . 'ims/items/2_2/extended_text.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 14
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_min.xml',
                array(
                    array('RESPONSE', 1, 0, '', array())
                )
            ),
            // # 15
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '', array())
                )
            ),
            // # 16
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_max.xml',
                array(
                    array('RESPONSE', 0, 2, '', array())
                )
            ),
            // # 17
            array(
                self::samplesDir() . 'custom/items/response_constraints/extended_text_patternmask.xml',
                array(
                    array('RESPONSE', 0, 0, '[\S]{10,15}', array())
                )
            ),
            // # 18
            array(
                self::samplesDir() . 'ims/items/2_2/extended_text_rubric.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 19
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackblock_adaptive.xml',
                array(
                    array('RESPONSE1', 0, 1, '', array()),
                    array('RESPONSE21', 0, 1, '', array()),
                    array('RESPONSE22', 0, 1, '', array()),
                    array('RESPONSE23', 0, 1, '', array()),
                    array('RESPONSE24', 0, 1, '', array()),
                    array('RESPONSE25', 0, 1, '', array()),
                    array('RESPONSE26', 0, 1, '', array()),
                    array('RESPONSE27', 0, 1, '', array())
                )
            ),
            // # 20
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackblock_solution_random.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 21
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackblock_templateblock.xml',
                array(
                    array('RESPONSE1', 0, 1, '', array())
                )
            ),
            // # 22
            array(
                self::samplesDir() . 'ims/items/2_2/feedbackInline.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 23
            array(
                self::samplesDir() . 'ims/items/2_2/gap_match.xml',
                array(
                    array('RESPONSE', 0, 0, '', array(
                        array('W', 0, 1),
                        array('Sp', 0, 1),
                        array('Su', 0, 1),
                        array('A', 0, 1),
                    ))
                )
            ),
            // # 24
            array(
                self::samplesDir() . 'ims/items/2_2/graphic_associate.xml',
                array(
                    array('RESPONSE', 0, 3, '', array(
                        array('A', 0, 3),
                        array('B', 0, 3),
                        array('C', 0, 3),
                        array('D', 0, 3),
                    ))
                )
            ),
            // # 25
            array(
                // maxConstraint is 1 because by default, maxAssociations = 1 in graphicAssociateInteraction.
                self::samplesDir() . 'custom/items/response_constraints/graphic_associate_min.xml',
                array(
                    array('RESPONSE', 1, 1, '', array(
                        array('A', 0, 3),
                        array('B', 0, 3),
                        array('C', 0, 3),
                        array('D', 0, 3),
                    ))
                )
            ),
            // # 26
            array(
                self::samplesDir() . 'custom/items/response_constraints/graphic_associate_min_max.xml',
                array(
                    array('RESPONSE', 1, 3, '', array(
                        array('A', 0, 3),
                        array('B', 0, 3),
                        array('C', 0, 3),
                        array('D', 0, 3),
                    ))
                )
            ),
            // # 27
            array(
                self::samplesDir() . 'ims/items/2_2/graphic_gap_match.xml',
                array(
                    array('RESPONSE', 0, 0, '', array(
                        array('CBG', 0, 1),
                        array('EBG', 0, 1),
                        array('EDI', 0, 1),
                        array('GLA', 0, 1),
                        array('MAN', 0, 1),
                        array('MCH', 0, 1),
                    ))
                )
            ),
            // # 28
            array(
                self::samplesDir() . 'ims/items/2_2/hotspot.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 29
            array(
                self::samplesDir() . 'custom/items/response_constraints/hotspot_default.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 30
            array(
                self::samplesDir() . 'custom/items/response_constraints/hotspot_min.xml',
                array(
                    array('RESPONSE', 2, 0, '', array())
                )
            ),
            // # 31
            array(
                self::samplesDir() . 'custom/items/response_constraints/hotspot_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '', array())
                )
            ),
            // # 32
            array(
                self::samplesDir() . 'ims/items/2_2/hottext.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 33
            array(
                self::samplesDir() . 'custom/items/response_constraints/hottext_default.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 34
            array(
                self::samplesDir() . 'custom/items/response_constraints/hottext_min.xml',
                array(
                    array('RESPONSE', 2, 0, '', array())
                )
            ),
            // # 35
            array(
                self::samplesDir() . 'custom/items/response_constraints/hottext_min_max.xml',
                array(
                    array('RESPONSE', 2, 2, '', array())
                )
            ),
            // # 36
            array(
                self::samplesDir() . 'ims/items/2_2/inline_choice.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 37
            array(
                self::samplesDir() . 'custom/items/response_constraints/inline_choice_required.xml',
                array(
                    array('RESPONSE', 1, 1, '', array())
                )
            ),
            // # 38
            array(
                self::samplesDir() . 'ims/items/2_2/likert.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 39
            array(
                self::samplesDir() . 'ims/items/2_2/match.xml',
                array(
                    array('RESPONSE', 0, 4, '', array(
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('P', 0, 1),
                        array('M', 0, 4),
                        array('R', 0, 4),
                        array('T', 0, 4),
                    ))
                )
            ),
            // # 40
            array(
                // maxAssociations = 1 because default for matchInteraction is 1.
                self::samplesDir() . 'custom/items/response_constraints/match_default.xml',
                array(
                    array('RESPONSE', 0, 1, '', array(
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('P', 0, 1),
                        array('M', 0, 4),
                        array('R', 0, 4),
                        array('T', 0, 4),
                    ))
                )
            ),
            // # 41
            array(
                // maxAssociations = 1 because default for matchInteraction is 1.
                self::samplesDir() . 'custom/items/response_constraints/match_min.xml',
                array(
                    array('RESPONSE', 1, 1, '', array(
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('P', 0, 1),
                        array('M', 0, 4),
                        array('R', 0, 4),
                        array('T', 0, 4),
                    ))
                )
            ),
            // # 42
            array(
                self::samplesDir() . 'custom/items/response_constraints/match_min_max.xml',
                array(
                    array('RESPONSE', 2, 3, '', array(
                        array('C', 0, 1),
                        array('D', 0, 1),
                        array('L', 0, 1),
                        array('P', 0, 1),
                        array('M', 0, 4),
                        array('R', 0, 4),
                        array('T', 0, 4),
                    ))
                )
            ),
            // # 43
            array(
                self::samplesDir() . 'ims/items/2_2/math.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 44
            array(
                self::samplesDir() . 'ims/items/2_2/mc_calc3.xml',
                array(
                    array('RESPONSE0', 1, 1, '', array())
                )
            ),
            // # 45
            array(
                self::samplesDir() . 'ims/items/2_2/mc_stat2.xml',
                array(
                    array('RESPONSE0', 0, 1, '', array()),
                    array('RESPONSE1', 0, 1, '', array()),
                    array('RESPONSE2', 0, 1, '', array()),
                    array('RESPONSE3', 0, 1, '', array())
                )
            ),
            // # 46
            array(
                self::samplesDir() . 'ims/items/2_2/modalFeedback.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 47
            array(
                self::samplesDir() . 'ims/items/2_2/multi-input.xml',
                array(
                    array('RESPONSE1', 0, 1, '', array()),
                    array('RESPONSE2', 0, 1, '', array()),
                    array('RESPONSE3', 0, 1, '', array()),
                    array('RESPONSE4', 0, 0, '', array(
                        array('F', 0, 1),
                        array('C', 0, 1),
                        array('S', 0, 1),
                        array('H', 0, 1),
                    ))
                )
            ),
            // # 48
            array(
                self::samplesDir() . 'ims/items/2_2/nested_object.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 49
            array(
                self::samplesDir() . 'ims/items/2_2/order.xml',
                array(
                    array('RESPONSE', 3, 0, '', array())
                )
            ),
            // # 50
            array(
                self::samplesDir() . 'custom/items/response_constraints/order_min.xml',
                array(
                    array('RESPONSE', 2, 0, '', array())
                )
            ),
            // # 51
            array(
                self::samplesDir() . 'custom/items/response_constraints/order_min_max.xml',
                array(
                    array('RESPONSE', 2, 3, '', array())
                )
            ),
            // # 52
            array(
                // Very special case. As per specs, in OrderInteraction, if minChoices is not specified,
                // maxChoices is ignored and all the choices must appear in response (must be ordered).
                self::samplesDir() . 'custom/items/response_constraints/order_max.xml',
                array(
                    array('RESPONSE', 3, 0, '', array())
                )
            ),
            // # 53
            array(
                self::samplesDir() . 'ims/items/2_2/order_rtl.xml',
                array(
                    array('RESPONSE', 3, 0, '', array())
                )
            ),
            // # 54
            array(
                self::samplesDir() . 'ims/items/2_2/orkney1.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 55
            array(
                self::samplesDir() . 'ims/items/2_2/orkney2.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 56
            array(
                self::samplesDir() . 'ims/items/2_2/position_object.xml',
                array(
                    array('RESPONSE', 0, 3, '', array())
                )
            ),
            // # 57
            array(
                self::samplesDir() . 'custom/items/response_constraints/position_object_min_max.xml',
                array(
                    array('RESPONSE', 2, 3, '', array())
                )
            ),
            // # 58
            array(
                self::samplesDir() . 'custom/items/response_constraints/position_object_min.xml',
                array(
                    array('RESPONSE', 2, 0, '', array())
                )
            ),
            // # 59
            array(
                self::samplesDir() . 'custom/items/response_constraints/position_object_default.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 60
            array(
                self::samplesDir() . 'ims/items/2_2/slider.xml',
                array()
            ),
            // # 61
            array(
                self::samplesDir() . 'ims/items/2_2/template.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 62
            array(
                self::samplesDir() . 'ims/items/2_2/text_entry.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 63
            array(
                self::samplesDir() . 'custom/items/response_constraints/text_entry_patternmask.xml',
                array(
                    array('RESPONSE', 0, 1, '[a-zA-Z]+')
                )
            ),
            // # 64
            array(
                self::samplesDir() . 'ims/items/2_2/select_point.xml',
                array(
                    array('RESPONSE', 0, 1, '', array())
                )
            ),
            // # 65
            array(
                self::samplesDir() . 'custom/items/response_constraints/select_point_default.xml',
                array(
                    array('RESPONSE', 0, 0, '', array())
                )
            ),
            // # 66
            array(
                self::samplesDir() . 'custom/items/response_constraints/select_point_min.xml',
                array(
                    array('RESPONSE', 2, 0, '', array())
                )
            ),
            // # 67
            array(
                self::samplesDir() . 'custom/items/response_constraints/select_point_min_max.xml',
                array(
                    array('RESPONSE', 3, 4, '', array())
                )
            ),
        );
    }
    
    public function testCreateAssessmentItemWrongIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The identifier argument must be a valid QTI Identifier, '999' given."
        );
        
        $assessmentItem = new AssessmentItem('999', 'Nine Nine Nine', false);
    }
    
    public function testCreateAssessmentItemWrongTitle()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The title argument must be a string, 'integer' given."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 9, false);
    }
    
    public function testCreateAssessmentItemWrongLanguage()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The lang argument must be a string, 'integer' given."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 'ABC', false, 1337);
    }
    
    public function testSetLabelWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The label argument must be a string with at most 256 characters."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setLabel(1337);
    }
    
    public function testSetAdaptiveWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The adaptive argument must be a boolean, 'integer' given."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setAdaptive(9999);
    }
    
    public function testSetTimeDependentWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The timeDependent argument must be a boolean, 'integer' given."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setTimeDependent(9999);
    }
    
    public function testSetToolNameWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The toolName argument must be a string with at most 256 characters."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setToolName(9999);
    }
    
    public function testSetToolVersionWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "The toolVersion argument must be a string with at most 256 characters."
        );
        
        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setToolVersion(9999);
    }
}
