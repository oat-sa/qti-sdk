<?php

declare(strict_types=1);

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\common\collections\IdentifierCollection;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentItemRef;
use qtism\data\content\ModalFeedbackRule;
use qtism\data\ExtendedAssessmentItemRef;
use qtism\data\ShowHide;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\ResponseValidityConstraint;
use qtism\data\state\Shuffling;
use qtism\data\state\ShufflingGroup;
use qtism\data\state\ShufflingGroupCollection;
use qtism\data\state\TemplateDeclaration;
use qtism\data\state\Weight;
use qtism\data\state\WeightCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class ExtendedAssessmentItemRefTest
 */
class ExtendedAssessmentItemRefTest extends QtiSmTestCase
{
    public function testCreateFromAssessmentItemRef(): void
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $extendedAssessmentItemRef = ExtendedAssessmentItemRef::createFromAssessmentItemRef($assessmentItemRef);

        $this::assertInstanceOf(ExtendedAssessmentItemRef::class, $extendedAssessmentItemRef);
        $this::assertEquals('Q01', $extendedAssessmentItemRef->getIdentifier());
        $this::assertEquals('Q01.xml', $extendedAssessmentItemRef->getHref());
    }

    /**
     * @depends testCreateFromAssessmentItemRef
     */
    public function testCreateFromAssessmentItemRefWithWeights(): void
    {
        $assessmentItemRef = new AssessmentItemRef('Q01', 'Q01.xml');
        $assessmentItemRef->setWeights(
            new WeightCollection(
                [
                    new Weight('WEIGHT', 2.),
                ]
            )
        );

        $extendedAssessmentItemRef = ExtendedAssessmentItemRef::createFromAssessmentItemRef($assessmentItemRef);
        $weights = $extendedAssessmentItemRef->getWeights();

        $this::assertCount(1, $weights);
        $this::assertEquals('WEIGHT', $weights['WEIGHT']->getIdentifier());
        $this::assertEquals(2., $weights['WEIGHT']->getValue());
    }

    public function testRemoveOutcomeDeclaration(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $outcomeDeclaration = new OutcomeDeclaration('OUTCOME', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addOutcomeDeclaration($outcomeDeclaration);

        $this::assertCount(1, $assessmentItemRef->getOutcomeDeclarations());
        $assessmentItemRef->removeOutcomeDeclaration($outcomeDeclaration);
        $this::assertCount(0, $assessmentItemRef->getOutcomeDeclarations());
    }

    public function testRemoveResponseDeclaration(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $responseDeclaration = new ResponseDeclaration('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addResponseDeclaration($responseDeclaration);

        $this::assertCount(1, $assessmentItemRef->getResponseDeclarations());
        $assessmentItemRef->removeResponseDeclaration($responseDeclaration);
        $this::assertCount(0, $assessmentItemRef->getResponseDeclarations());
    }

    public function testAddTemplateDeclaration(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $templateDeclaration = new TemplateDeclaration('TEMPLATE', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addTemplateDeclaration($templateDeclaration);

        $this::assertCount(1, $assessmentItemRef->getTemplateDeclarations());
    }

    public function testRemoveTemplateDeclaration(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $templateDeclaration = new TemplateDeclaration('TEMPLATE', BaseType::IDENTIFIER, Cardinality::SINGLE);
        $assessmentItemRef->addTemplateDeclaration($templateDeclaration);

        $this::assertCount(1, $assessmentItemRef->getTemplateDeclarations());
        $assessmentItemRef->removeTemplateDeclaration($templateDeclaration);
        $this::assertCount(0, $assessmentItemRef->getTemplateDeclarations());
    }

    public function testRemoveModalFeedbackRule(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $modalFeedbackRule = new ModalFeedbackRule('OUTCOME', ShowHide::SHOW, 'MDLF');
        $assessmentItemRef->addModalFeedbackRule($modalFeedbackRule);

        $this::assertCount(1, $assessmentItemRef->getModalFeedbackRules());
        $assessmentItemRef->removeModalFeedbackRule($modalFeedbackRule);
        $this::assertCount(0, $assessmentItemRef->getModalFeedbackRules());
    }

    public function testRemoveShuffling(): void
    {
        $shuffling = new Shuffling(
            'RESPONSE',
            new ShufflingGroupCollection(
                [
                    new ShufflingGroup(
                        new IdentifierCollection(
                            ['ID1', 'ID2', 'ID3']
                        )
                    ),
                ]
            )
        );

        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $assessmentItemRef->addShuffling($shuffling);
        $this::assertCount(1, $assessmentItemRef->getShufflings());
        $assessmentItemRef->removeShuffling($shuffling);
        $this::assertCount(0, $assessmentItemRef->getShufflings());
    }

    public function testSetAdaptiveWrongType(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The adaptive argument must be a boolean value, 'string' given.");

        $assessmentItemRef->setAdaptive('true');
    }

    public function testSetTimeDependentWrongType(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The timeDependent argument must be a boolean value, 'string' given.");

        $assessmentItemRef->setTimeDependent('true');
    }

    public function testAddResponseValidityConstraint(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', 0, 1);
        $assessmentItemRef->addResponseValidityConstraint($responseValidityConstraint);
        $this::assertCount(1, $assessmentItemRef->getResponseValidityConstraints());
    }

    public function testRemoveResponseValidityConstraint(): void
    {
        $assessmentItemRef = new ExtendedAssessmentItemRef('Q01', 'Q01.xml');
        $responseValidityConstraint = new ResponseValidityConstraint('RESPONSE', 0, 1);
        $assessmentItemRef->addResponseValidityConstraint($responseValidityConstraint);
        $this::assertCount(1, $assessmentItemRef->getResponseValidityConstraints());
        $assessmentItemRef->removeResponseValidityConstraint($responseValidityConstraint);
        $this::assertCount(0, $assessmentItemRef->getResponseValidityConstraints());
    }
}
