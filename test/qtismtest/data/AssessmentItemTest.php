<?php

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\data\AssessmentItem;
use qtism\data\content\ModalFeedback;
use qtism\data\content\ModalFeedbackCollection;
use qtism\data\ShowHide;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentItemTest
 */
class AssessmentItemTest extends QtiSmTestCase
{
    public function testModalFeedbackRules()
    {
        $assessmentItem = new AssessmentItem('Q01', 'Question 1', false);

        $modalFeedback1 = new ModalFeedback('LOOKUP', 'SHOWME');
        $modalFeedback2 = new ModalFeedback('LOOKUP2', 'HIDEME');
        $modalFeedback2->setShowHide(ShowHide::HIDE);
        $assessmentItem->setModalFeedbacks(new ModalFeedbackCollection([$modalFeedback1, $modalFeedback2]));

        $modalFeedbackRules = $assessmentItem->getModalFeedbackRules();
        $this::assertCount(2, $modalFeedbackRules);

        $this::assertEquals('LOOKUP', $modalFeedbackRules[0]->getOutcomeIdentifier());
        $this::assertEquals('SHOWME', $modalFeedbackRules[0]->getIdentifier());
        $this::assertEquals(ShowHide::SHOW, $modalFeedbackRules[0]->getShowHide());

        $this::assertEquals('LOOKUP2', $modalFeedbackRules[1]->getOutcomeIdentifier());
        $this::assertEquals('HIDEME', $modalFeedbackRules[1]->getIdentifier());
        $this::assertEquals(ShowHide::HIDE, $modalFeedbackRules[1]->getShowHide());
    }

    /**
     * @dataProvider getResponseValidityConstraintsProvider
     * @param $path
     * @param array $expected
     * @throws XmlStorageException
     */
    public function testGetResponseValidityConstraints($path, array $expected)
    {
        $doc = new XmlDocument();
        $doc->load($path);

        $assessmentItem = $doc->getDocumentComponent();
        $responseValidityConstraints = $assessmentItem->getResponseValidityConstraints();

        $this::assertEquals(count($expected), count($responseValidityConstraints));

        for ($i = 0; $i < count($responseValidityConstraints); $i++) {
            $this::assertEquals($expected[$i][0], $responseValidityConstraints[$i]->getResponseIdentifier());
            $this::assertEquals($expected[$i][1], $responseValidityConstraints[$i]->getMinConstraint(), 'minConstraint failed for ' . $expected[$i][0]);
            $this::assertEquals($expected[$i][2], $responseValidityConstraints[$i]->getMaxConstraint(), 'maxConstraint failed for ' . $expected[$i][0]);
            $this::assertEquals($expected[$i][3], $responseValidityConstraints[$i]->getPatternMask());

            if (isset($expected[$i][4])) {
                // Let's check association constraints.
                $expectedAssociationValidityConstraints = $expected[$i][4];
                $associationValidityConstraints = $responseValidityConstraints[$i]->getAssociationValidityConstraints();

                $this::assertEquals(count($expectedAssociationValidityConstraints), count($associationValidityConstraints));

                for ($j = 0; $j < count($associationValidityConstraints); $j++) {
                    $this::assertEquals($expectedAssociationValidityConstraints[$j][0], $associationValidityConstraints[$j]->getIdentifier());
                    $this::assertEquals($expectedAssociationValidityConstraints[$j][1], $associationValidityConstraints[$j]->getMinConstraint());
                    $this::assertEquals($expectedAssociationValidityConstraints[$j][2], $associationValidityConstraints[$j]->getMaxConstraint());
                }
            }
        }
    }

    /**
     * @return array
     */
    public function getResponseValidityConstraintsProvider()
    {
        return [
            // # 0
            [
                self::samplesDir() . 'ims/items/2_2/choice.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 1
            [
                self::samplesDir() . 'custom/items/response_constraints/choice_min_max.xml',
                [
                    ['RESPONSE', 2, 2, '', []],
                ],
            ],
            // # 2
            [
                self::samplesDir() . 'custom/items/response_constraints/choice_min.xml',
                [
                    ['RESPONSE', 2, 0, '', []],
                ],
            ],
            // # 3
            [
                self::samplesDir() . 'custom/items/response_constraints/choice_default.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 4
            [
                self::samplesDir() . 'ims/items/2_2/adaptive.xml',
                [
                    ['DOOR', 0, 1, '', []],
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 5
            [
                self::samplesDir() . 'ims/items/2_2/adaptive_template.xml',
                [
                    ['DOOR', 0, 1, '', []],
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 6
            [
                self::samplesDir() . 'ims/items/2_2/associate.xml',
                [
                    [
                        'RESPONSE',
                        0,
                        3,
                        '',
                        [
                            ['A', 0, 1],
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['M', 0, 1],
                            ['P', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 7
            [
                self::samplesDir() . 'custom/items/response_constraints/associate_min_max.xml',
                [
                    [
                        'RESPONSE',
                        2,
                        3,
                        '',
                        [
                            ['A', 0, 1],
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['M', 0, 1],
                            ['P', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 8
            [
                self::samplesDir() . 'custom/items/response_constraints/associate_min.xml',
                [
                    // maxConstraint is 1 because by default, maxAssociations = 1 in associateInteraction.
                    [
                        'RESPONSE',
                        1,
                        1,
                        '',
                        [
                            ['A', 0, 1],
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['M', 0, 1],
                            ['P', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 9
            [
                self::samplesDir() . 'custom/items/response_constraints/associate_default.xml',
                [
                    // maxConstraint is 1 because by default, maxAssociations = 1 in associateInteraction.
                    [
                        'RESPONSE',
                        0,
                        1,
                        '',
                        [
                            ['A', 0, 1],
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['M', 0, 1],
                            ['P', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 10
            [
                self::samplesDir() . 'ims/items/2_2/choice_fixed.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 11
            [
                self::samplesDir() . 'ims/items/2_2/choice_multiple.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 12
            [
                self::samplesDir() . 'ims/items/2_2/choice_multiple_rtl.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 13
            [
                // Default for minStrings/maxStrings.
                self::samplesDir() . 'ims/items/2_2/extended_text.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 14
            [
                self::samplesDir() . 'custom/items/response_constraints/extended_text_min.xml',
                [
                    ['RESPONSE', 1, 0, '', []],
                ],
            ],
            // # 15
            [
                self::samplesDir() . 'custom/items/response_constraints/extended_text_min_max.xml',
                [
                    ['RESPONSE', 2, 2, '', []],
                ],
            ],
            // # 16
            [
                self::samplesDir() . 'custom/items/response_constraints/extended_text_max.xml',
                [
                    ['RESPONSE', 0, 2, '', []],
                ],
            ],
            // # 17
            [
                self::samplesDir() . 'custom/items/response_constraints/extended_text_patternmask.xml',
                [
                    ['RESPONSE', 0, 0, '[\S]{10,15}', []],
                ],
            ],
            // # 18
            [
                self::samplesDir() . 'ims/items/2_2/extended_text_rubric.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 19
            [
                self::samplesDir() . 'ims/items/2_2/feedbackblock_adaptive.xml',
                [
                    ['RESPONSE1', 0, 1, '', []],
                    ['RESPONSE21', 0, 1, '', []],
                    ['RESPONSE22', 0, 1, '', []],
                    ['RESPONSE23', 0, 1, '', []],
                    ['RESPONSE24', 0, 1, '', []],
                    ['RESPONSE25', 0, 1, '', []],
                    ['RESPONSE26', 0, 1, '', []],
                    ['RESPONSE27', 0, 1, '', []],
                ],
            ],
            // # 20
            [
                self::samplesDir() . 'ims/items/2_2/feedbackblock_solution_random.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 21
            [
                self::samplesDir() . 'ims/items/2_2/feedbackblock_templateblock.xml',
                [
                    ['RESPONSE1', 0, 1, '', []],
                ],
            ],
            // # 22
            [
                self::samplesDir() . 'ims/items/2_2/feedbackInline.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 23
            [
                self::samplesDir() . 'ims/items/2_2/gap_match.xml',
                [
                    [
                        'RESPONSE',
                        0,
                        0,
                        '',
                        [
                            ['choice_1', 0, 1],
                            ['choice_2', 0, 1],
                            ['choice_3', 0, 1],
                            ['choice_4', 0, 1],
                            ['choice_5', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 24
            [
                self::samplesDir() . 'ims/items/2_2/graphic_associate.xml',
                [
                    [
                        'RESPONSE',
                        0,
                        3,
                        '',
                        [
                            ['A', 0, 3],
                            ['B', 0, 3],
                            ['C', 0, 3],
                            ['D', 0, 3],
                        ],
                    ],
                ],
            ],
            // # 25
            [
                // maxConstraint is 1 because by default, maxAssociations = 1 in graphicAssociateInteraction.
                self::samplesDir() . 'custom/items/response_constraints/graphic_associate_min.xml',
                [
                    [
                        'RESPONSE',
                        1,
                        1,
                        '',
                        [
                            ['A', 0, 3],
                            ['B', 0, 3],
                            ['C', 0, 3],
                            ['D', 0, 3],
                        ],
                    ],
                ],
            ],
            // # 26
            [
                self::samplesDir() . 'custom/items/response_constraints/graphic_associate_min_max.xml',
                [
                    [
                        'RESPONSE',
                        1,
                        3,
                        '',
                        [
                            ['A', 0, 3],
                            ['B', 0, 3],
                            ['C', 0, 3],
                            ['D', 0, 3],
                        ],
                    ],
                ],
            ],
            // # 27
            [
                self::samplesDir() . 'ims/items/2_2/graphic_gap_match.xml',
                [
                    [
                        'RESPONSE',
                        0,
                        0,
                        '',
                        [
                            ['CBG', 0, 1],
                            ['EBG', 0, 1],
                            ['EDI', 0, 1],
                            ['GLA', 0, 1],
                            ['MAN', 0, 1],
                            ['MCH', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 28
            [
                self::samplesDir() . 'ims/items/2_2/hotspot.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 29
            [
                self::samplesDir() . 'custom/items/response_constraints/hotspot_default.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 30
            [
                self::samplesDir() . 'custom/items/response_constraints/hotspot_min.xml',
                [
                    ['RESPONSE', 2, 0, '', []],
                ],
            ],
            // # 31
            [
                self::samplesDir() . 'custom/items/response_constraints/hotspot_min_max.xml',
                [
                    ['RESPONSE', 2, 2, '', []],
                ],
            ],
            // # 32
            [
                self::samplesDir() . 'ims/items/2_2/hottext.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 33
            [
                self::samplesDir() . 'custom/items/response_constraints/hottext_default.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 34
            [
                self::samplesDir() . 'custom/items/response_constraints/hottext_min.xml',
                [
                    ['RESPONSE', 2, 0, '', []],
                ],
            ],
            // # 35
            [
                self::samplesDir() . 'custom/items/response_constraints/hottext_min_max.xml',
                [
                    ['RESPONSE', 2, 2, '', []],
                ],
            ],
            // # 36
            [
                self::samplesDir() . 'ims/items/2_2/inline_choice.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 37
            [
                self::samplesDir() . 'custom/items/response_constraints/inline_choice_required.xml',
                [
                    ['RESPONSE', 1, 1, '', []],
                ],
            ],
            // # 38
            [
                self::samplesDir() . 'ims/items/2_2/likert.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 39
            [
                self::samplesDir() . 'ims/items/2_2/match.xml',
                [
                    [
                        'RESPONSE',
                        0,
                        4,
                        '',
                        [
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['P', 0, 1],
                            ['M', 0, 4],
                            ['R', 0, 4],
                            ['T', 0, 4],
                        ],
                    ],
                ],
            ],
            // # 40
            [
                // maxAssociations = 1 because default for matchInteraction is 1.
                self::samplesDir() . 'custom/items/response_constraints/match_default.xml',
                [
                    [
                        'RESPONSE',
                        0,
                        1,
                        '',
                        [
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['P', 0, 1],
                            ['M', 0, 4],
                            ['R', 0, 4],
                            ['T', 0, 4],
                        ],
                    ],
                ],
            ],
            // # 41
            [
                // maxAssociations = 1 because default for matchInteraction is 1.
                self::samplesDir() . 'custom/items/response_constraints/match_min.xml',
                [
                    [
                        'RESPONSE',
                        1,
                        1,
                        '',
                        [
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['P', 0, 1],
                            ['M', 0, 4],
                            ['R', 0, 4],
                            ['T', 0, 4],
                        ],
                    ],
                ],
            ],
            // # 42
            [
                self::samplesDir() . 'custom/items/response_constraints/match_min_max.xml',
                [
                    [
                        'RESPONSE',
                        2,
                        3,
                        '',
                        [
                            ['C', 0, 1],
                            ['D', 0, 1],
                            ['L', 0, 1],
                            ['P', 0, 1],
                            ['M', 0, 4],
                            ['R', 0, 4],
                            ['T', 0, 4],
                        ],
                    ],
                ],
            ],
            // # 43
            [
                self::samplesDir() . 'ims/items/2_2/math.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 44
            [
                self::samplesDir() . 'ims/items/2_2/mc_calc3.xml',
                [
                    ['RESPONSE0', 1, 1, '', []],
                ],
            ],
            // # 45
            [
                self::samplesDir() . 'ims/items/2_2/mc_stat2.xml',
                [
                    ['RESPONSE0', 0, 1, '', []],
                    ['RESPONSE1', 0, 1, '', []],
                    ['RESPONSE2', 0, 1, '', []],
                    ['RESPONSE3', 0, 1, '', []],
                ],
            ],
            // # 46
            [
                self::samplesDir() . 'ims/items/2_2/modalFeedback.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 47
            [
                self::samplesDir() . 'ims/items/2_2/multi-input.xml',
                [
                    ['RESPONSE1', 0, 1, '', []],
                    ['RESPONSE2', 0, 1, '', []],
                    ['RESPONSE3', 0, 1, '', []],
                    [
                        'RESPONSE4',
                        0,
                        0,
                        '',
                        [
                            ['F', 0, 1],
                            ['C', 0, 1],
                            ['S', 0, 1],
                            ['H', 0, 1],
                        ],
                    ],
                ],
            ],
            // # 48
            [
                self::samplesDir() . 'ims/items/2_2/nested_object.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 49
            [
                self::samplesDir() . 'ims/items/2_2/order.xml',
                [
                    ['RESPONSE', 3, 0, '', []],
                ],
            ],
            // # 50
            [
                self::samplesDir() . 'custom/items/response_constraints/order_min.xml',
                [
                    ['RESPONSE', 2, 0, '', []],
                ],
            ],
            // # 51
            [
                self::samplesDir() . 'custom/items/response_constraints/order_min_max.xml',
                [
                    ['RESPONSE', 2, 3, '', []],
                ],
            ],
            // # 52
            [
                // Very special case. As per specs, in OrderInteraction, if minChoices is not specified,
                // maxChoices is ignored and all the choices must appear in response (must be ordered).
                self::samplesDir() . 'custom/items/response_constraints/order_max.xml',
                [
                    ['RESPONSE', 3, 0, '', []],
                ],
            ],
            // # 53
            [
                self::samplesDir() . 'ims/items/2_2/order_rtl.xml',
                [
                    ['RESPONSE', 3, 0, '', []],
                ],
            ],
            // # 54
            [
                self::samplesDir() . 'ims/items/2_2/orkney1.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 55
            [
                self::samplesDir() . 'ims/items/2_2/orkney2.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 56
            [
                self::samplesDir() . 'ims/items/2_2/position_object.xml',
                [
                    ['RESPONSE', 0, 3, '', []],
                ],
            ],
            // # 57
            [
                self::samplesDir() . 'custom/items/response_constraints/position_object_min_max.xml',
                [
                    ['RESPONSE', 2, 3, '', []],
                ],
            ],
            // # 58
            [
                self::samplesDir() . 'custom/items/response_constraints/position_object_min.xml',
                [
                    ['RESPONSE', 2, 0, '', []],
                ],
            ],
            // # 59
            [
                self::samplesDir() . 'custom/items/response_constraints/position_object_default.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 60
            [
                self::samplesDir() . 'ims/items/2_2/slider.xml',
                [],
            ],
            // # 61
            [
                self::samplesDir() . 'ims/items/2_2/template.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 62
            [
                self::samplesDir() . 'ims/items/2_2/text_entry.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 63
            [
                self::samplesDir() . 'custom/items/response_constraints/text_entry_patternmask.xml',
                [
                    ['RESPONSE', 0, 1, '[a-zA-Z]+'],
                ],
            ],
            // # 64
            [
                self::samplesDir() . 'ims/items/2_2/select_point.xml',
                [
                    ['RESPONSE', 0, 1, '', []],
                ],
            ],
            // # 65
            [
                self::samplesDir() . 'custom/items/response_constraints/select_point_default.xml',
                [
                    ['RESPONSE', 0, 0, '', []],
                ],
            ],
            // # 66
            [
                self::samplesDir() . 'custom/items/response_constraints/select_point_min.xml',
                [
                    ['RESPONSE', 2, 0, '', []],
                ],
            ],
            // # 67
            [
                self::samplesDir() . 'custom/items/response_constraints/select_point_min_max.xml',
                [
                    ['RESPONSE', 3, 4, '', []],
                ],
            ],
            // # 68
            [
                self::samplesDir() . 'ims/items/2_2/graphic_order.xml',
                [
                    ['RESPONSE', 4, 0, '', []],
                ],
            ],
        ];
    }

    public function testCreateAssessmentItemWrongIdentifier()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The identifier argument must be a valid QTI Identifier, '999' given.");

        $assessmentItem = new AssessmentItem('999', 'Nine Nine Nine', false);
    }

    public function testCreateAssessmentItemWrongTitle()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The title argument must be a string, 'integer' given.");

        $assessmentItem = new AssessmentItem('ABC', 9, false);
    }

    public function testCreateAssessmentItemWrongLanguage()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The lang argument must be a string, 'integer' given.");

        $assessmentItem = new AssessmentItem('ABC', 'ABC', false, 1337);
    }

    public function testSetLabelWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The label argument must be a string with at most 256 characters.');

        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setLabel(str_repeat('1337', 65));
    }

    public function testSetAdaptiveWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The adaptive argument must be a boolean, 'integer' given.");

        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setAdaptive(9999);
    }

    public function testSetTimeDependentWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The timeDependent argument must be a boolean, 'integer' given.");

        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setTimeDependent(9999);
    }

    public function testSetToolNameWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The toolName argument must be a string with at most 256 characters.');

        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setToolName(str_repeat('tool', 65));
    }

    public function testSetToolVersionWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The toolVersion argument must be a string with at most 256 characters.');

        $assessmentItem = new AssessmentItem('ABC', 'ABC', false);
        $assessmentItem->setToolVersion(str_repeat('1337', 65));
    }
}
