<?php
namespace qtismtest\data\storage\xml\marshalling;

use qtism\data\ShowHide;
use qtismtest\QtiSmTestCase;
use qtism\data\content\InlineCollection;
use qtism\data\content\xhtml\text\Strong;
use qtism\data\content\TextRun;
use qtism\data\content\FlowStaticCollection;
use qtism\data\content\interactions\SimpleAssociableChoice;
use \DOMDocument;

class SimpleAssociableChoiceMarshallerTest extends QtiSmTestCase {

	public function testMarshall21() {
		$simpleChoice = new SimpleAssociableChoice('choice_1', 1);
		$simpleChoice->setClass('qti-simpleAssociableChoice');
		$strong = new Strong();
		$strong->setContent(new InlineCollection(array(new TextRun('strong'))));
	    $simpleChoice->setContent(new FlowStaticCollection(array(new TextRun('This is ... '), $strong, new TextRun('!'))));
	    
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
	    $element = $marshaller->marshall($simpleChoice);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMax="1">This is ... <strong>strong</strong>!</simpleAssociableChoice>', $dom->saveXML($element));
	}
	
	/**
	 * @depends testMarshall21
	 */
	public function testMarshallMatchMin21() {
	    $simpleChoice = new SimpleAssociableChoice('choice_1', 3);
	    $simpleChoice->setMatchMin(2);
	    $simpleChoice->setContent(new FlowStaticCollection(array(new TextRun('Choice #1'))));
	     
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($simpleChoice);
	    $element = $marshaller->marshall($simpleChoice);
	     
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="3" matchMin="2">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
	}
	
	public function testUnmarshall21() {
	    $element = $this->createDOMElement('
	        <simpleAssociableChoice class="qti-simpleAssociableChoice" identifier="choice_1" matchMin="1" matchMax="2">This is ... <strong>strong</strong>!</simpleAssociableChoice>
	    ');
	    
	    $marshaller = $this->getMarshallerFactory('2.1.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	    
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\SimpleAssociableChoice', $component);
	    $this->assertEquals('qti-simpleAssociableChoice', $component->getClass());
	    $this->assertEquals('choice_1', $component->getIdentifier());
	    $this->assertEquals(1, $component->getMatchMin());
	    $this->assertEquals(2, $component->getMatchMax());
	    
	    $content = $component->getContent();
	    $this->assertInstanceOf('qtism\\data\\content\\FlowStaticCollection', $content);
	    $this->assertEquals(3, count($content));
	}
	
	public function testMarshall20() {
	    $simpleChoice = new SimpleAssociableChoice('choice_1', 1);
	    $simpleChoice->setContent(new FlowStaticCollection(array(new TextRun('Choice #1'))));
	     
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
	    $element = $marshaller->marshall($simpleChoice);
	     
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="1">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
	}
	
	/**
	 * @depends testMarshall20
	 */
	public function testMarshallNoTemplateIdentifierNoShowHideNoMatchMin20() {
	    // Aims at testing that attributes templateIdentifier, showHide, matchMin
	    // are never in the output in a QTI 2.0 context.
	    $simpleChoice = new SimpleAssociableChoice('choice_1', 3);
	    $simpleChoice->setMatchMin(2);
	    $simpleChoice->setContent(new FlowStaticCollection(array(new TextRun('Choice #1'))));
	    $simpleChoice->setTemplateIdentifier('XTEMPLATE');
	    $simpleChoice->setShowHide(ShowHide::HIDE);
	    
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($simpleChoice);
	    $element = $marshaller->marshall($simpleChoice);
	    
	    $dom = new DOMDocument('1.0', 'UTF-8');
	    $element = $dom->importNode($element, true);
	    $this->assertEquals('<simpleAssociableChoice identifier="choice_1" matchMax="3">Choice #1</simpleAssociableChoice>', $dom->saveXML($element));
	}
	
	public function testUnmarshall20() {
	    $element = $this->createDOMElement('
	        <simpleAssociableChoice identifier="choice_1" matchMax="2">Choice #1</simpleAssociableChoice>
	    ');
	     
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	     
	    $this->assertInstanceOf('qtism\\data\\content\\interactions\\SimpleAssociableChoice', $component);
	    $this->assertEquals('choice_1', $component->getIdentifier());
	    $this->assertEquals(0, $component->getMatchMin());
	    $this->assertEquals(2, $component->getMatchMax());
	     
	    $content = $component->getContent();
	    $this->assertInstanceOf('qtism\\data\\content\\FlowStaticCollection', $content);
	    $this->assertEquals(1, count($content));
	}
	
	/**
	 * @depends testUnmarshall20
	 */
	public function testUnmarshallNoTemplateIdentifierShowHideMatchMinInfluence20() {
	    // Aims at testing that matchMin, showHide and templateIdentifier attributes
	    // have no influence in a QTI 2.0 context.
	    $element = $this->createDOMElement('
	        <simpleAssociableChoice identifier="choice_1" matchMin="2" matchMax="3" templateIdentifier="XTEMPLATE" showHide="hide">Choice #1</simpleAssociableChoice>
	    ');
	
	    $marshaller = $this->getMarshallerFactory('2.0.0')->createMarshaller($element);
	    $component = $marshaller->unmarshall($element);
	    
	    $this->assertEquals(0, $component->getMatchMin());
	    $this->assertFalse($component->hasTemplateIdentifier());
	    $this->assertEquals(ShowHide::SHOW, $component->getShowHide());
	}
}