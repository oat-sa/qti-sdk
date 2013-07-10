<?php

use qtism\data\state\ResponseDeclarationCollection;
use qtism\data\state\OutcomeDeclarationCollection;
use qtism\data\state\ResponseDeclaration;
use qtism\data\state\OutcomeDeclaration;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\AssessmentItem;

use \DOMDocument;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class AssessmentItemMarshallerTest extends QtiSmTestCase {

	public function testMarshallMinimal() {

		$identifier = 'Q01';
		$timeDependent = false;
		$assessmentItem = new AssessmentItem($identifier, $timeDependent);
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($assessmentItem);
		$element = $marshaller->marshall($assessmentItem);
		
		$this->assertInstanceOf('\DOMElement', $element);
		$this->assertEquals('assessmentItem', $element->nodeName);
		
		// adaptive, timeDependent, identifier
		$this->assertEquals($element->attributes->length, 3);
		$this->assertEquals($identifier, $element->getAttribute('identifier'));
		$this->assertEquals('false', $element->getAttribute('timeDependent'));
		$this->assertEquals('false', $element->getAttribute('adaptive'));
	}
	
	public function testUnmarshallMinimal() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML(
			'
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" timeDependent="false"/>
			');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\assessmentItem', $component);
		$this->assertEquals('Q01', $component->getIdentifier());
		$this->assertEquals(false, $component->isTimeDependent());
		$this->assertEquals(false, $component->isAdaptive());
		$this->assertFalse($component->hasLang());
	}
	
	public function testMarshallMaximal() {
		$identifier = 'Q01';
		$timeDependent = true;
		$adaptive = true;
		$lang = 'en-YO'; // Yoda English ;)
		
		$responseDeclarations = new ResponseDeclarationCollection();
		$responseDeclarations[] = new ResponseDeclaration('resp1', BaseType::INTEGER, Cardinality::SINGLE);
		$responseDeclarations[] = new ResponseDeclaration('resp2', BaseType::FLOAT, Cardinality::SINGLE);
		
		$outcomeDeclarations = new OutcomeDeclarationCollection();
		$outcomeDeclarations[] = new OutcomeDeclaration('out1', BaseType::BOOLEAN, Cardinality::MULTIPLE);
		$outcomeDeclarations[] = new OutcomeDeclaration('out2', BaseType::IDENTIFIER, Cardinality::SINGLE);
		
		$item = new AssessmentItem($identifier, $timeDependent, $lang);
		$item->setAdaptive($adaptive);
		$item->setResponseDeclarations($responseDeclarations);
		$item->setOutcomeDeclarations($outcomeDeclarations);
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($item);
		$element = $marshaller->marshall($item);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('assessmentItem', $element->nodeName);
		
		// adaptive, timeDependent, identifier, lang
		$this->assertEquals($element->attributes->length, 4);
		$this->assertEquals($identifier, $element->getAttribute('identifier'));
		$this->assertEquals('true', $element->getAttribute('timeDependent'));
		$this->assertEquals('true', $element->getAttribute('adaptive'));
		$this->assertEquals($lang, $element->getAttribute('lang'));
		
		$responseDeclarationElts = $element->getElementsByTagName('responseDeclaration');
		$this->assertEquals(2, $responseDeclarationElts->length);
		$this->assertEquals('resp1', $responseDeclarationElts->item(0)->getAttribute('identifier'));
		$this->assertEquals('resp2', $responseDeclarationElts->item(1)->getAttribute('identifier'));
		
		$outcomeDeclarationElts = $element->getElementsByTagName('outcomeDeclaration');
		$this->assertEquals(2, $outcomeDeclarationElts->length);
		$this->assertEquals('out1', $outcomeDeclarationElts->item(0)->getAttribute('identifier'));
		$this->assertEquals('out2', $outcomeDeclarationElts->item(1)->getAttribute('identifier'));
	}
	
	public function testUnmarshallMaximal() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML(
			'
			<assessmentItem xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" identifier="Q01" timeDependent="false" adaptive="false" lang="en-YO">
				<responseDeclaration identifier="resp1" baseType="integer" cardinality="single"/>
				<responseDeclaration identifier="resp2" baseType="float" cardinality="single"/>
				<outcomeDeclaration identifier="out1" baseType="boolean" cardinality="multiple"/>
				<outcomeDeclaration identifier="out2" baseType="identifier" cardinality="single"/>
			</assessmentItem>
			');
		$element = $dom->documentElement;
	
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
	
		$this->assertInstanceOf('qtism\\data\\assessmentItem', $component);
		$this->assertEquals('Q01', $component->getIdentifier());
		$this->assertEquals(false, $component->isTimeDependent());
		$this->assertEquals(false, $component->isAdaptive());
		$this->assertTrue($component->hasLang());
		$this->assertEquals('en-YO', $component->getLang());
		
		$responseDeclarations = $component->getResponseDeclarations();
		$this->assertEquals(2, count($responseDeclarations));
		
		$outcomeDeclarations = $component->getOutcomeDeclarations();
		$this->assertEquals(2, count($outcomeDeclarations));
	}
}