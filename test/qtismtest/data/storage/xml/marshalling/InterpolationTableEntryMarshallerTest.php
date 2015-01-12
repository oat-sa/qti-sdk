<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\state\InterpolationTableEntry;
use qtism\data\state\Value;
use qtism\common\enums\BaseType;
use \DOMDocument;

class InterpolationTableEntryMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {

		$sourceValue = 23.445;
		$baseType = BaseType::INTEGER; // fake baseType of a container variableDeclaration element.
		$value = 243;
		$targetValue = $value;
		
		$component = new InterpolationTableEntry($sourceValue, $targetValue);
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($component, array($baseType));
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('interpolationTableEntry', $element->nodeName);
		$this->assertEquals($sourceValue . '', $element->getAttribute('sourceValue'));
		$this->assertEquals($targetValue . '' , $element->getAttribute('targetValue'));
		$this->assertEquals('true', $element->getAttribute('includeBoundary'));
	}
	
	public function testUnmarshall() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<interpolationTableEntry xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" sourceValue="243.3" targetValue="1"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element, array(BaseType::INTEGER)); // With fake variableDeclaration baseType.
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\state\\InterpolationTableEntry', $component);
		$this->assertEquals(243.3, $component->getSourceValue());
		$this->assertInternalType('float', $component->getSourceValue());
		$this->assertInternalType('integer', $component->getTargetValue());
		$this->assertEquals(1, $component->getTargetValue());
	}
}