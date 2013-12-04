<?php

use qtism\data\content\interactions\TextEntryInteraction;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class TextEntryInteractionMarshallerTest extends QtiSmTestCase {

	public function testMarshallMinimal() {
	    
	    $textEntryInteraction = new TextEntryInteraction('RESPONSE');
	    
        $element = $this->getMarshallerFactory()->createMarshaller($textEntryInteraction)->marshall($textEntryInteraction);
        
        $dom = new DOMDocument('1.0', 'UTF-8');
        $element = $dom->importNode($element, true);
        $this->assertEquals('<textEntryInteraction responseIdentifier="RESPONSE"/>', $dom->saveXML($element));
	}
	
	public function testUnmarshallMinimal() {
        $element = $this->createDOMElement('<textEntryInteraction responseIdentifier="RESPONSE"/>');
        $textEntryInteraction = $this->getMarshallerFactory()->createMarshaller($element)->unmarshall($element);
        $this->assertInstanceOf('qtism\\data\\content\\interactions\\TextEntryInteraction', $textEntryInteraction);
        $this->assertEquals('RESPONSE', $textEntryInteraction->getResponseIdentifier());
        $this->assertEquals(10, $textEntryInteraction->getBase());
        $this->assertFalse($textEntryInteraction->hasStringIdentifier());
        $this->assertFalse($textEntryInteraction->hasExpectedLength());
        $this->assertFalse($textEntryInteraction->hasPatternMask());
        $this->assertFalse($textEntryInteraction->hasPlaceholderText());
	}
}