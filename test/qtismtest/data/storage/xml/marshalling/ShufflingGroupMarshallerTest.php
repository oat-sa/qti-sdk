<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use qtism\data\state\ShufflingGroup;
use qtism\common\collections\IdentifierCollection;
use \DOMDocument;

class ShufflingGroupMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {
		
		$component = new ShufflingGroup(new IdentifierCollection(array('id1', 'id2', 'id3')));
		$factory = new CompactMarshallerFactory();
		$marshaller = $factory->createMarshaller($component);
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('id1 id2 id3', $element->nodeValue);
	}
	
	public function testUnmarshall() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<shufflingGroup>id1 id2 id3</shufflingGroup>');
		$element = $dom->documentElement;
		
		$factory = new CompactMarshallerFactory();
		$marshaller = $factory->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\state\\ShufflingGroup', $component);
		$identifiers = $component->getIdentifiers();
		$this->assertEquals(array('id1', 'id2', 'id3'), $identifiers->getArrayCopy());
	}
}
