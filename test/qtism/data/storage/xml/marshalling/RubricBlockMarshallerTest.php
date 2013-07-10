<?php

use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\RubricBlock;
use qtism\data\Stylesheet;
use qtism\data\StylesheetCollection;
use qtism\data\View;
use qtism\data\ViewCollection;
use \DOMDocument;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class RubricBlockMarshallerTest extends QtiSmTestCase {

	public function testMarshallOne() {

		$views = new ViewCollection();
		$views[] = View::CANDIDATE;
		
		$component = new RubricBlock($views);
		$component->setContent('<p>This is a rubricBlock!</p>');
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component);
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('rubricBlock', $element->nodeName);
		$this->assertEquals('', $element->getAttribute('use'));
		$this->assertEquals('candidate', $element->getAttribute('view'));
		
		$stylesheetElements = $element->getElementsByTagName('stylesheet');
		$this->assertEquals(0, $stylesheetElements->length);
		
		$contentElts = $element->getElementsByTagName('p');
		$this->assertEquals($contentElts->length, 1);
		$this->assertEquals('This is a rubricBlock!', $contentElts->item(0)->nodeValue);
	}
	
	public function testUnmarshall() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML(
			'
			<rubricBlock xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" view="candidate tutor" use="This is a mock">
				<stylesheet href="http://myuri.com#1"/>
				<stylesheet href="http://myuri.com#2"/>
				<p>This is a rubricBlock!</p>
			</rubricBlock>
			'
		);
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\RubricBlock', $component);
		$this->assertEquals('This is a mock', $component->getUse());
		$this->assertEquals(count($component->getStylesheets()), 2);
		$this->assertEquals(count($component->getViews()), 2);
		$views = $component->getViews();
		$this->assertEquals($views[0], View::CANDIDATE);
		$this->assertEquals($views[1], View::TUTOR);
		$this->assertEquals('<p>This is a rubricBlock!</p>', $component->getContent());
	}
}