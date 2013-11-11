<?php

use qtism\data\content\xhtml\text\Abbr;
use qtism\runtime\rendering\markup\xhtml\XhtmlRenderingContext;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class XhtmlRenderingContextTest extends QtiSmTestCase {
	
	public function testGetRenderer() {
	    
	    $ctx = new XhtmlRenderingContext();
	    $abbr = new Abbr();
	    $renderer = $ctx->getRenderer($abbr);
	    
	    $this->assertInstanceOf('qtism\\runtime\\rendering\\markup\\xhtml\\BodyElementRenderer', $renderer);
	}
}