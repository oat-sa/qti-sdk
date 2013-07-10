<?php

use qtism\data\storage\xml\marshalling\Marshaller;
use qtism\data\TimeLimits;
use \DOMDocument;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class TimeLimitsMarshallerTest extends QtiSmTestCase {

	public function testMarshall() {

		$minTime = 50;
		$maxTime = 100;
		
		$component = new TimeLimits($minTime, $maxTime);
		$marshaller = $this->getMarshallerFactory()->createMarshaller($component);
		$element = $marshaller->marshall($component);
		
		$this->assertInstanceOf('\\DOMElement', $element);
		$this->assertEquals('timeLimits', $element->nodeName);
		$this->assertEquals($minTime, $element->getAttribute('minTime'));
		$this->assertEquals($maxTime, $element->getAttribute('maxTime'));
		$this->assertEquals('false', $element->getAttribute('allowLateSubmission'));
	}
	
	public function testUnmarshall() {
		$dom = new DOMDocument('1.0', 'UTF-8');
		$dom->loadXML('<timeLimits xmlns="http://www.imsglobal.org/xsd/imsqti_v2p1" minTime="50" maxTime="100"/>');
		$element = $dom->documentElement;
		
		$marshaller = $this->getMarshallerFactory()->createMarshaller($element);
		$component = $marshaller->unmarshall($element);
		
		$this->assertInstanceOf('qtism\\data\\TimeLimits', $component);
		$this->assertEquals($component->getMinTime(), '50');
		$this->assertEquals($component->getMaxTime(), '100');
		$this->assertEquals($component->doesAllowLateSubmission(), false);
	}
}