<?php
namespace qtismtest\runtime\rules;

use qtismtest\QtiSmTestCase;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\State;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\rules\SetCorrectResponseProcessor;
use qtism\runtime\rules\RuleProcessingException;

class setCorrectValueProcessorTest extends QtiSmTestCase {
	
	public function testSetCorrectResponseSimple() {
		$rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSE">
				<baseValue baseType="identifier">ChoiceA</baseValue>
			</setCorrectResponse>
		');
		
		$processor = new SetCorrectResponseProcessor($rule);
		$response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
		$state = new State(array($response));
		$processor->setState($state);
		$processor->process();
		
		$this->assertInstanceOf('qtism\\common\\datatypes\\QtiIdentifier', $state->getVariable('RESPONSE')->getCorrectResponse());
		$this->assertEquals('ChoiceA', $state->getVariable('RESPONSE')->getCorrectResponse()->getValue());
	}
	
	public function testSetCorrectResponseNoVariable() {
	    $rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSEXXXX">
				<baseValue baseType="identifier">ChoiceA</baseValue>
			</setCorrectResponse>
		');
	    
	    $processor = new SetCorrectResponseProcessor($rule);
	    $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
	    $state = new State(array($response));
	    $processor->setState($state);
	    
	    $this->setExpectedException(
	        'qtism\\runtime\\rules\\RuleProcessingException',
	        "No variable with identifier 'RESPONSEXXXX' to be set in the current state.",
	        RuleProcessingException::NONEXISTENT_VARIABLE
	    );
	    
	    $processor->process();
	}
	
	public function testSetCorrectResponseWrongBaseType() {
	    $rule = $this->createComponentFromXml('
			<setCorrectResponse identifier="RESPONSE">
				<baseValue baseType="boolean">true</baseValue>
			</setCorrectResponse>
		');
	     
	    $processor = new SetCorrectResponseProcessor($rule);
	    $response = new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER);
	    $state = new State(array($response));
	    $processor->setState($state);
	     
	    $this->setExpectedException(
            'qtism\\runtime\\rules\\RuleProcessingException'
	    );
	     
	    $processor->process();
	}
}