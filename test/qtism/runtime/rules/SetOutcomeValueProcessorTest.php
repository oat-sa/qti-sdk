<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\common\State;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\rules\SetOutcomeValueProcessor;

class setOutcomeValueProcessorTest extends QtiSmTestCase {
	
	public function testSetOutcomeValueSimple() {
		$rule = $this->createComponentFromXml('
			<setOutcomeValue identifier="SCORE">
				<baseValue baseType="float">4.3</baseValue>
			</setOutcomeValue>
		');
		
		$processor = new SetOutcomeValueProcessor($rule);
		$score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT);
		$state = new State(array($score));
		$processor->setState($state);
		$processor->process();
		
		// The state must be modified.
		// OutcomeVariable with identifier 'SCORE' must contain 4.3.
		$this->assertInternalType('float', $state['SCORE']);
		$this->assertEquals(4.3, $state['SCORE']);
	}
	
	public function testSetOutcomeValueModerate() {
		$rule = $this->createComponentFromXml('
			<setOutcomeValue identifier="myBool">
				<member>
					<baseValue baseType="string">Incredible!</baseValue>
					<multiple>
						<baseValue baseType="string">This...</baseValue>
						<baseValue baseType="string">Is...</baseValue>
						<baseValue baseType="string">Incredible!</baseValue>
					</multiple>
				</member>
			</setOutcomeValue>
		');
		
		$processor = new SetOutcomeValueProcessor($rule);
		$myBool = new OutcomeVariable('myBool', Cardinality::SINGLE, BaseType::BOOLEAN, false);
		$state = new State(array($myBool));
		$this->assertFalse($state['myBool']);
		
		$processor->setState($state);
		$processor->process();
		$this->assertInternalType('boolean', $state['myBool']);
		$this->assertTrue($state['myBool']);
	}
}