<?php

use qtism\runtime\rendering\markup\xhtml\BodyElementRenderer;
use qtism\data\content\xhtml\text\Br;
use qtism\data\content\xhtml\text\Abbr;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingContext;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class BodyElementRendererTest extends QtiSmTestCase {
	
	public function testRenderNoChildren() {
	    $ctx = new XhtmlRenderingContext();
	    $br = new Br('my-br', 'break down', 'en-US', 'QTISM generated.');
	    
	    $renderer = new BodyElementRenderer();
	    $renderer->setRenderingContext($ctx);
	    
	    $element = $renderer->render($br)->firstChild;
	    
	    $this->assertEquals('my-br', $element->getAttribute('id'));
	    $this->assertEquals('break down', $element->getAttribute('class'));
	    $this->assertEquals('en-US', $element->getAttribute('lang'));
	    $this->assertEquals('', $element->getAttribute('label'));
	}
	
	public function testRenderChildren() {
	}
}