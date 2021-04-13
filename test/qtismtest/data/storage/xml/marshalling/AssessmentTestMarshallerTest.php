<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\common\enums\BaseType;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\AssessmentTest;
use qtism\data\content\FlowCollection;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\TextRun;
use qtism\data\content\xhtml\text\Div;
use qtism\data\expressions\BaseValue;
use qtism\data\processing\OutcomeProcessing;
use qtism\data\rules\OutcomeRuleCollection;
use qtism\data\rules\SetOutcomeValue;
use qtism\data\state\OutcomeDeclaration;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\TestFeedback;
use qtism\data\TestFeedbackCollection;
use qtism\data\TestPart;
use qtism\data\TestPartCollection;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentTestMarshallerTest
 */
class AssessmentTestMarshallerTest extends QtiSmTestCase
{
    public function testMarshall()
    {
        $identifier = 'myAssessmentTest';
        $title = 'My Assessment Test';
        $toolName = 'QTIStateMachine';
        $toolVersion = '1.0b';

        $assessmentSections = new AssessmentSectionCollection();
        $assessmentSections[] = new AssessmentSection('myAssessmentSection', 'My Assessment Section', true);

        $testParts = new TestPartCollection();
        $testParts[] = new TestPart('myTestPart', $assessmentSections);

        $div = new Div();
        $div->setContent(new FlowCollection([new TextRun('Feedback!')]));
        $testFeedBacks = new TestFeedbackCollection();
        $testFeedBacks[] = new TestFeedback('myFeedback', 'myOutcome', new FlowStaticCollection([$div]), 'A Feedback');

        $outcomeRules = new OutcomeRuleCollection();
        $outcomeRules[] = new SetOutcomeValue('myOutcome', new BaseValue(BaseType::BOOLEAN, true));
        $outcomeProcessing = new OutcomeProcessing($outcomeRules);

        $outcomeDeclarations = new OutcomeDeclarationCollection();
        $outcomeDeclarations[] = new OutcomeDeclaration('myOutcome', BaseType::BOOLEAN);

        $component = new AssessmentTest($identifier, $title, $testParts);
        $component->setToolName($toolName);
        $component->setToolVersion($toolVersion);
        $component->setTestFeedbacks($testFeedBacks);
        $component->setOutcomeProcessing($outcomeProcessing);
        $component->setOutcomeDeclarations($outcomeDeclarations);

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('assessmentTest', $element->nodeName);
        $this::assertEquals($identifier, $element->getAttribute('identifier'));
        $this::assertEquals($title, $element->getAttribute('title'));
        $this::assertEquals($toolName, $element->getAttribute('toolName'));
        $this::assertEquals($toolVersion, $element->getAttribute('toolVersion'));

        // testParts
        $this::assertEquals(1, $element->getElementsByTagName('testPart')->length);
        $this::assertSame($element, $element->getElementsByTagName('testPart')->item(0)->parentNode);

        // assessmentSections
        $testPart = $element->getElementsByTagName('testPart')->item(0);
        $this::assertEquals(1, $element->getElementsByTagName('assessmentSection')->length);
        $this::assertSame($testPart, $element->getElementsByTagName('assessmentSection')->item(0)->parentNode);

        // outcomeDeclarations
        $this::assertEquals(1, $element->getElementsByTagName('outcomeDeclaration')->length);
        $this::assertSame($element, $element->getElementsByTagName('outcomeDeclaration')->item(0)->parentNode);

        // testFeedbacks
        $this::assertEquals(1, $element->getElementsByTagName('testFeedback')->length);
        $this::assertSame($element, $element->getElementsByTagName('testFeedback')->item(0)->parentNode);

        // outcomeProcessing
        $this::assertEquals(1, $element->getElementsByTagName('outcomeProcessing')->length);
        $this::assertSame($element, $element->getElementsByTagName('outcomeProcessing')->item(0)->parentNode);
    }

    public function testUnmarshall()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<assessmentTest identifier="myAssessmentTest" title="My Assessment Test" toolName="QTIStateMachine" toolVersion="1.0b">
				<testPart identifier="myTestPart" navigationMode="linear" submissionMode="individual">
					<assessmentSection identifier="myAssessmentSection" title="My Assessment Section" visible="true"/>
				</testPart>
				<testFeedback showHide="true" access="during" outcomeIdentifier="myOutcome" identifier="myFeedback" title="A Feedback">
					<div>Feedback!</div>
				</testFeedback>
				<outcomeDeclaration identifier="myOutcome" baseType="boolean" cardinality="single"/>
				<outcomeProcessing>
					<setOutcomeValue identifier="myOutcome">
						<baseValue baseType="boolean">true</baseValue>
					</setOutcomeValue>
  				</outcomeProcessing>
			</assessmentTest>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(AssessmentTest::class, $component);
        $this::assertEquals('myAssessmentTest', $component->getIdentifier());
        $this::assertEquals('My Assessment Test', $component->getTitle());
        $this::assertEquals('QTIStateMachine', $component->getToolName());
        $this::assertEquals('1.0b', $component->getToolVersion());
        $this::assertTrue($component->isExclusivelyLinear());

        $this::assertCount(1, $component->getTestFeedbacks());
        $this::assertCount(1, $component->getTestParts());
        $this::assertCount(1, $component->getOutcomeDeclarations());
        $this::assertInstanceOf(OutcomeProcessing::class, $component->getOutcomeProcessing());
    }
}
