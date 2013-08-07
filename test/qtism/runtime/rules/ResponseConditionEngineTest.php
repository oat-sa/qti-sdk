<?php
use qtism\runtime\common\ResponseVariable;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\State;
use qtism\runtime\rules\ResponseConditionEngine;
use qtism\runtime\rules\RuleProcessingException;

class ResponseConditionEngineTest extends QtiSmTestCase {
	
	/**
	 * @dataProvider responseConditionMatchCorrectProvider
	 * 
	 * @param string $response A QTI Identifier
	 * @param float $expectedScore The expected score for a given $response
	 */
	public function testResponseConditionMatchCorrect($response, $expectedScore) {
		
		$rule = $this->createComponentFromXml('
			<responseCondition>
				<responseIf>
					<match>
						<variable identifier="RESPONSE"/>
						<correct identifier="RESPONSE"/>
					</match>
					<setOutcomeValue identifier="SCORE">
						<baseValue baseType="float">1</baseValue>
						</setOutcomeValue>
				</responseIf>
				<responseElse>
					<setOutcomeValue identifier="SCORE">
						<baseValue baseType="float">0</baseValue>
					</setOutcomeValue>
				</responseElse>
			</responseCondition>
		');
		
		$responseVarDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
				<correctResponse>
					<value>ChoiceA</value>
				</correctResponse>
			</responseDeclaration>
		');
		$responseVar = ResponseVariable::createFromDataModel($responseVarDeclaration);
		$this->assertEquals('ChoiceA', $responseVar->getCorrectResponse());
		
		// Set 'ChoiceA' to 'RESPONSE' in order to get a score of 1.0.
		$responseVar->setValue($response);
		
		$outcomeVarDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
				<defaultValue>
					<value>0</value>
				</defaultValue>
			</outcomeDeclaration>		
		');
		$outcomeVar = OutcomeVariable::createFromDataModel($outcomeVarDeclaration);
		$this->assertEquals(0, $outcomeVar->getDefaultValue());
		
		$state = new State(array($responseVar, $outcomeVar));
		$engine = new ResponseConditionEngine($rule, $state);
		$engine->process();
		
		$this->assertInternalType('float', $state['SCORE']);
		$this->assertEquals($expectedScore, $state['SCORE']);
	}
	
	public function responseConditionMatchCorrectProvider() {
		return array(
			array('ChoiceA', 1.0),
			array('ChoiceB', 0.0),
			array('ChoiceC', 0.0),
			array('ChoiceD', 0.0)
		);
	}
}