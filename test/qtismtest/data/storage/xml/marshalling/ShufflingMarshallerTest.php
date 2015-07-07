<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtismtest\QtiSmTestCase;
use qtism\data\storage\xml\marshalling\CompactMarshallerFactory;
use qtism\data\state\ShufflingGroupCollection;
use qtism\data\state\Shuffling;
use qtism\common\collections\IdentifierCollection;
use qtism\data\state\ShufflingGroup;
use \DOMDocument;

class ShufflingMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {
	
	    $shufflingGroup1 = new ShufflingGroup(new IdentifierCollection(array('id1', 'id2', 'id3')));
	    $shufflingGroup2 = new ShufflingGroup(new IdentifierCollection(array('id4', 'id5', 'id6')));
	    $shuffling = new Shuffling('RESPONSE', new ShufflingGroupCollection(array($shufflingGroup1, $shufflingGroup2)));
	    
	    $factory = new CompactMarshallerFactory();
	    $element = $factory->createMarshaller($shuffling)->marshall($shuffling);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    
	    $this->assertEquals('<shuffling responseIdentifier="RESPONSE"><shufflingGroup>id1 id2 id3</shufflingGroup><shufflingGroup>id4 id5 id6</shufflingGroup></shuffling>', $dom->saveXML($element));
	    

	}
	
	public function testUnmarshall() {
	    $element = $this->createDOMElement('
	        <shuffling responseIdentifier="RESPONSE"><shufflingGroup>id1 id2 id3</shufflingGroup><shufflingGroup>id4 id5 id6</shufflingGroup></shuffling>                
	    ');
	    
	    $factory = new CompactMarshallerFactory();
	    $component = $factory->createMarshaller($element)->unmarshall($element);
	    
	    $this->assertInstanceOf('\\qtism\\data\\state\\Shuffling', $component);
	    $this->assertEquals('RESPONSE', $component->getResponseIdentifier());
	    
	    $groups = $component->getShufflingGroups();
	    $this->assertEquals(2, count($groups));
	    
	    $this->assertEquals(array('id1', 'id2', 'id3'), $groups[0]->getIdentifiers()->getArrayCopy());
	    $this->assertEquals(array('id4', 'id5', 'id6'), $groups[1]->getIdentifiers()->getArrayCopy());
	}
}