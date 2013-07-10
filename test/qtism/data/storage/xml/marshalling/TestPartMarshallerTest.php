<?php

use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\TestPart;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\TestFeedback;
use qtism\data\TestFeedbackCollection;
use qtism\data\NavigationMode;
use qtism\data\SubmissionMode;
use \DOMDocument;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class TestPartMarshallerTest extends QtiSmTestCase {

	public function testMarshallMinimal() {

		$section1 = new AssessmentSection('section1', 'My Section 1', true);
		$section2 = new AssessmentSection('section2', 'My Section 2', false);
		
		$component = new TestPart('part1',
								  new AssessmentSectionCollection(array($section1, $section2)),
								  NavigationMode::LINEAR,
								  SubmissionMode::INDIVIDUAL);
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component);
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('testPart', $element->nodeName);
		$this->assertEquals('part1', $element->getAttribute('identifier'));
		$this->assertEquals('linear', $element->getAttribute('navigationMode'));
		$this->assertEquals('individual', $element->getAttribute('submissionMode'));
		$this->assertEquals(2, $element->getElementsByTagName('assessmentSection')->length);
		$this->assertEquals('section2', $element->getElementsByTagName('assessmentSection')->item(1)->getAttribute('identifier'));
	}
	
	public function testUnmarshallMinimal() {
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
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\TestPart', $component);
		$this->assertEquals('part1', $component->getIdentifier());
		$this->assertEquals(NavigationMode::LINEAR, $component->getNavigationMode());
		$this->assertEquals(SubmissionMode::INDIVIDUAL, $component->getSubmissionMode());
		
		$assessmentSections = $component->getAssessmentSections();
		$this->assertInstanceOf('qtism\\data\\AssessmentSection', $assessmentSections[0]);
		$this->assertEquals('section1', $assessmentSections[0]->getIdentifier());
		$this->assertInstanceOf('qtism\\data\\AssessmentSection', $assessmentSections[1]);
		$this->assertEquals('section2', $assessmentSections[1]->getIdentifier());
	}
	
	public function testUnmarshallMorderate() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML(
			'
			<testPart xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="part1" navigationMode="linear" submissionMode="individual">
				<assessmentSection identifier="section1" title="My Section 1" visible="true">
					<assessmentItemRef identifier="Q01" href="Q01.xml" fixed="false"/>
					<assessmentItemRef identifier="Q02" href="Q02.xml" fixed="false"/>
					<assessmentItemRef identifier="Q03" href="Q03.xml" fixed="false"/>
				</assessmentSection>
			</testPart>
			'
		);
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
	
		$this->assertInstanceOf('qtism\\data\\TestPart', $component);
		$this->assertEquals('part1', $component->getIdentifier());
		$this->assertEquals(NavigationMode::LINEAR, $component->getNavigationMode());
		$this->assertEquals(SubmissionMode::INDIVIDUAL, $component->getSubmissionMode());
		
		$assessmentSections = $component->getAssessmentSections();
		$this->assertInstanceOf('qtism\\data\\AssessmentSection', $assessmentSections[0]);
		$this->assertEquals('section1', $assessmentSections[0]->getIdentifier());
		
		$assessmentSection = $assessmentSections[0];
		$this->assertEquals(3, count($assessmentSection->getSectionParts()));
	}
}