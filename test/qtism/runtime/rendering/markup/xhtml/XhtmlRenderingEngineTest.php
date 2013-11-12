<?php

use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingEngine;

require_once (dirname(__FILE__) . '/../../../../../QtiSmTestCase.php');

class XhtmlRenderingEngineTest extends QtiSmTestCase {
	
	public function testVerySimple() {
	    $div = $this->createComponentFromXml('
	        <div id="my-div" class="container">bla bla</div>
	    ');
	    
	    $renderingEngine = new XhtmlRenderingEngine();
	    $rendering = $renderingEngine->render($div);
	    
	    $this->assertInstanceOf('\\DOMDocument', $rendering);
	    
	    $divElt = $rendering->documentElement;
	    $this->assertEquals('div', $divElt->nodeName);
	    $this->assertEquals('my-div', $divElt->getAttribute('id'));
	    $this->assertEquals('container', $divElt->getAttribute('class'));
	    
	    $text = $divElt->firstChild;
	    $this->assertInstanceOf('\\DOMText', $text);
	    $this->assertEquals('bla bla', $text->wholeText);
	}
}