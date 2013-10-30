<?php

use qtism\data\content\xhtml\text\Em;
use qtism\data\content\TextRun;
use qtism\data\content\InlineCollection;
use qtism\data\content\xhtml\text\Strong;
use \DOMDocument;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class SimpleInlineMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {
		$strong = new Strong();
		$strong->setContent(new InlineCollection(array(new TextRun('John Dunbar'))));
		
		$em = new Em();
		$em->setContent(new InlineCollection(array(new TextRun('He is '), $strong, new TextRun('.'))));
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($em);
		$element = $marshaller->marshall($em);
		$dom = new DOMDocument('1.0', 'UTF-8');
		$element = $dom->importNode($element, true);
		
		$this->assertEquals('<em>He is <strong>John Dunbar</strong>.</em>', $dom->saveXML($element));
	}
	
	public function testUnmarshall() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<em>He is <strong>John Dunbar</strong>.</em>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$em = $marshaller->unmarshall($element);
		$this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Em', $em);
		
		$sentence = $em->getContent();
		$this->assertInstanceOf('qtism\\data\\content\\InlineCollection', $sentence);
		$this->assertEquals(3, count($sentence));
		
		$this->assertInstanceOf('qtism\\data\\content\\TextRun', $sentence[0]);
		$this->assertEquals('He is ', $sentence[0]->getContent());
		
		$this->assertInstanceOf('qtism\\data\\content\\xhtml\\text\\Strong', $sentence[1]);
		$strongContent = $sentence[1]->getContent();
		$this->assertEquals('John Dunbar', $strongContent[0]->getContent());
		
		$this->assertInstanceOf('qtism\\data\\content\\TextRun', $sentence[2]);
		$this->assertEquals('.', $sentence[2]->getContent());
	}
}