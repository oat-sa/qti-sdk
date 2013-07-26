<?php

use qtism\runtime\common\RecordContainer;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

use qtism\runtime\common\OutcomeVariable;
use qtism\common\enums\BaseType;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\expressions\processing\MapResponseProcessor;
use qtism\common\datatypes\Pair;
use qtism\runtime\common\MultipleContainer;

class MapResponseProcessorTest extends QtiSmTestCase {
	
	public function testSimple() {
		$variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="integer" cardinality="single">
				<mapping>
					<mapEntry mapKey="0" mappedValue="1"/>
					<mapEntry mapKey="1" mappedValue="2"/>
				</mapping>
			</responseDeclaration>
		');
		$variable = ResponseVariable::createFromDataModel($variableDeclaration);
		$mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
		
		$state = new State();
		$state->setVariable($variable);
		$mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
		$mapResponseProcessor->setState($state);
		
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		// The variable has no value so the default mapping value is returned.
		$this->assertEquals(0, $result); 
		
		$state['response1'] = 0;
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1, $result);
		
		$state['response1'] = 1;
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(2, $result);
		
		$state['response1'] = 240;
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(0, $result);
	}
	
	public function testMultipleComplexTyping() {
		$variableDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="response1" baseType="pair" cardinality="multiple">
				<mapping defaultValue="1">
					<mapEntry mapKey="A B" mappedValue="1.5"/>
					<mapEntry mapKey="C D" mappedValue="2.5"/>
				</mapping>
			</responseDeclaration>
		');
		$variable = ResponseVariable::createFromDataModel($variableDeclaration);
		$mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
		$mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
		
		$state = new State();
		$state->setVariable($variable);
		$mapResponseProcessor->setState($state);
		
		// Should return the mapping defaultValue because 'response1' = null.
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1, $result);
		
		$state['response1'] = new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'B')));
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(1.5, $result);
		
		$state['response1'][] = new Pair('C', 'D');
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(4, $result);
		
		// mapEntries must be taken into account only once, as per QTI 2.1 spec.
		$state['response1'][] = new Pair('C', 'D');
		$result = $mapResponseProcessor->process();
		$this->assertInternalType('float', $result);
		$this->assertEquals(4, $result); // 2.5 taken into account only once!
	}
	
	public function testVariableNotDefined() {
		$this->setExpectedException('qtism\runtime\expressions\processing\ExpressionProcessingException');
		$mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="INVALID"/>');
		$mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
		$mapResponseProcessor->process();
	}
	
	public function testNoMapping() {
		$this->setExpectedException('qtism\runtime\expressions\processing\ExpressionProcessingException');
		$variableDeclaration = $this->createComponentFromXml('<responseDeclaration identifier="response1" baseType="duration" cardinality="multiple"/>');
		$variable = ResponseVariable::createFromDataModel($variableDeclaration);
		$mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
		$mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
		
		$mapResponseProcessor->setState(new State(array($variable)));
		$mapResponseProcessor->process();
	}
	
	public function testOutcomeDeclaration() {
		$this->setExpectedException('qtism\runtime\expressions\processing\ExpressionProcessingException');
		$variableDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="response1" baseType="integer" cardinality="multiple">
				<mapping>
					<mapEntry mapKey="0" mappedValue="0.0"/>
				</mapping>
			</outcomeDeclaration>
		');
		$variable = OutcomeVariable::createFromDataModel($variableDeclaration);
		$mapResponseExpr = $this->createComponentFromXml('<mapResponse identifier="response1"/>');
		$mapResponseProcessor = new MapResponseProcessor($mapResponseExpr);
		
		$mapResponseProcessor->setState(new State(array($variable)));
		$mapResponseProcessor->process();
	}
}