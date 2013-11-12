<?php

use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingContext;
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
	
	public function testIgnoreClassesOne() {
	   
	   $renderingEngine = new XhtmlRenderingEngine();
	   $renderingEngine->ignoreQtiClasses('h1');
	    
	   $div = $this->createComponentFromXml('
	       <div>
              <h1>I will be ignored...</h1>
	          <p>I am alive!</p>
	       </div>
	   ');
	   
	   $divElt = $renderingEngine->render($div)->documentElement;
	   $this->assertEquals('div', $divElt->nodeName);
	   
	   $h1s = $divElt->getElementsByTagName('h1');
	   $this->assertEquals(0, $h1s->length);
	   
	   $ps = $divElt->getElementsByTagName('p');
	   $this->assertEquals(1, $ps->length);
	}
}