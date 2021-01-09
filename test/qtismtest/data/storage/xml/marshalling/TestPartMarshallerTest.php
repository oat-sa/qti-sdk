<?php

namespace qtismtest\data\storage\xml\marshalling;

use DOMDocument;
use DOMElement;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;
use qtism\data\TestPart;
use qtismtest\QtiSmTestCase;
use qtism\data\expressions\operators\NotOperator;
use qtism\data\expressions\operators\Equal;

/**
 * Class TestPartMarshallerTest
 */
class TestPartMarshallerTest extends QtiSmTestCase
{
    public function testMarshallMinimal()
    {
        $section1 = new AssessmentSection('section1', 'My Section 1', true);
        $section2 = new AssessmentSection('section2', 'My Section 2', false);

        $component = new TestPart(
            'part1',
            new AssessmentSectionCollection([$section1, $section2]),
            NavigationMode::LINEAR,
            SubmissionMode::INDIVIDUAL
        );

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component);
        $element = $marshaller->marshall($component);

        $this::assertInstanceOf(DOMElement::class, $element);
        $this::assertEquals('testPart', $element->nodeName);
        $this::assertEquals('part1', $element->getAttribute('identifier'));
        $this::assertEquals('linear', $element->getAttribute('navigationMode'));
        $this::assertEquals('individual', $element->getAttribute('submissionMode'));
        $this::assertEquals(2, $element->getElementsByTagName('assessmentSection')->length);
        $this::assertEquals('section2', $element->getElementsByTagName('assessmentSection')->item(1)->getAttribute('identifier'));
    }

    public function testUnmarshallMinimal()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<testPart xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="part1" navigationMode="linear" submissionMode="individual">
				<assessmentSection identifier="section1" title="My Section 1" visible="true"/>
				<assessmentSection identifier="section2" title="My Section 2" visible="false"/>
			</testPart>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(TestPart::class, $component);
        $this::assertEquals('part1', $component->getIdentifier());
        $this::assertEquals(NavigationMode::LINEAR, $component->getNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $component->getSubmissionMode());

        $assessmentSections = $component->getAssessmentSections();
        $this::assertInstanceOf(AssessmentSection::class, $assessmentSections['section1']);
        $this::assertEquals('section1', $assessmentSections['section1']->getIdentifier());
        $this::assertInstanceOf(AssessmentSection::class, $assessmentSections['section2']);
        $this::assertEquals('section2', $assessmentSections['section2']->getIdentifier());
    }

    public function testUnmarshallMorderate()
    {
        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadXML(
            '
			<testPart xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="part1" navigationMode="linear" submissionMode="individual">
		        <preCondition>
		            <not>
		                <baseValue baseType="boolean">true</baseValue>
		            </not>
		        </preCondition>
		        <branchRule target="Q05">
		            <equal>
		                <sum>
		                    <baseValue baseType="integer">1</baseValue>
		                    <baseValue baseType="integer">1</baseValue>
		                </sum>
		                <baseValue baseType="integer">2</baseValue>
		            </equal>
		        </branchRule>
		        <itemSessionControl maxAttempts="0"/>
		        <timeLimits minTime="60" maxTime="120" allowLateSubmission="true"/>
				<assessmentSection identifier="section1" title="My Section 1" visible="true">
					<selection select="3"/>
		            <assessmentItemRef identifier="Q01" href="Q01.xml" fixed="false"/>
					<assessmentItemRef identifier="Q02" href="Q02.xml" fixed="false"/>
					<assessmentItemRef identifier="Q03" href="Q03.xml" fixed="false"/>
				</assessmentSection>
		        <testFeedback identifier="feedback1" access="atEnd" outcomeIdentifier="outcome1" showHide="show">
		            <div>Feedback1</div>
		        </testFeedback>
		        <testFeedback identifier="feedback2" access="atEnd" outcomeIdentifier="outcome2" showHide="show">
		            <div>Feedback2</div>
		        </testFeedback>
			</testPart>
			'
        );
        $element = $dom->documentElement;

        $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
        $component = $marshaller->unmarshall($element);

        $this::assertInstanceOf(TestPart::class, $component);
        $this::assertEquals('part1', $component->getIdentifier());
        $this::assertEquals(NavigationMode::LINEAR, $component->getNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $component->getSubmissionMode());
        $this::assertTrue($component->hasTimeLimits());

        $assessmentSections = $component->getAssessmentSections();
        $this::assertInstanceOf(AssessmentSection::class, $assessmentSections['section1']);
        $this::assertEquals('section1', $assessmentSections['section1']->getIdentifier());
        $this::assertTrue($assessmentSections['section1']->hasSelection());

        $assessmentSection = $assessmentSections['section1'];
        $this::assertEquals(3, count($assessmentSection->getSectionParts()));

        $branchRules = $component->getBranchRules();
        $this::assertEquals(1, count($branchRules));
        $this::assertEquals('Q05', $branchRules[0]->getTarget());
        $branchRuleCondition = $this::assertInstanceOf(Equal::class, $branchRules[0]->getExpression());

        $preConditions = $component->getPreConditions();
        $this::assertEquals(1, count($preConditions));
        $this::assertInstanceOf(NotOperator::class, $preConditions[0]->getExpression());

        $this::assertTrue($component->hasItemSessionControl());
        $this::assertEquals(0, $component->getItemSessionControl()->getMaxAttempts());
        $this::assertTrue($component->hasTimeLimits());
    }
}
