<?php

use qtism\runtime\expressions\NumberPresentedProcessor;

require_once (dirname(__FILE__) . '/../../../QtiSmItemSubsetTestCase.php');

class NumberPresentedProcessorTest extends QtiSmItemSubsetTestCase {
	
	public function testNumberPresented() {
		$session = $this->getTestSession();
		
		$expression = $this->createComponentFromXml('<numberPresented/>');
		$processor = new NumberPresentedProcessor($expression);
		$processor->setState($session);
		
		// At the moment, nothing presented.
		$result = $processor->process();
		$this->assertEquals(0, $result);
	}
}