<?php
namespace qtismtest\runtime\processing;

use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\Identifier;
use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\common\ProcessingException;
use qtism\runtime\common\State;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\processing\ResponseProcessingEngine;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;

class ResponseProcessingEngineTest extends QtiSmTestCase {
	
	public function testResponseProcessingMatchCorrect() {
		$responseProcessing = $this->createComponentFromXml('
			<responseProcessing template="http://www.imsglobal.org/question/qti_v2p1/rptemplates/match_correct"/>
		');
		
		$responseDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier">
				<correctResponse>
					<value>ChoiceA</value>
				</correctResponse>
			</responseDeclaration>		
		');
		
		$outcomeDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float">
				<defaultValue>
					<value>0</value>
				</defaultValue>
			</outcomeDeclaration>
		');
		
		$respVar = ResponseVariable::createFromDataModel($responseDeclaration);
		$outVar = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		$context = new State(array($respVar, $outVar));
		
		$engine = new ResponseProcessingEngine($responseProcessing, $context);
		
		// --> answer as a correct response.
		$context['RESPONSE'] = new Identifier('ChoiceA');
		$engine->process();
		$this->assertInstanceOf('qtism\\common\\datatypes\\Float', $context['SCORE']);
		$this->assertEquals(1.0, $context['SCORE']->getValue());
	}
	
	public function testResponseProcessingExitResponse() {
	    $responseProcessing = $this->createComponentFromXml('
	        <responseProcessing>
	            <setOutcomeValue identifier="OUTCOME">
	                <baseValue baseType="integer">1</baseValue>
	            </setOutcomeValue>
                <exitResponse/>
	            <!-- Should never be executed! -->
	            <setOutcomeValue identifier="OUTCOME">
	                <baseValue baseType="integer">2</baseValue>
	            </setOutcomeValue>
	        </responseProcessing>
	    ');
	    
	    $state = new State();
	    $state->setVariable(new OutcomeVariable('OUTCOME', Cardinality::SINGLE, BaseType::INTEGER));
	    $engine = new ResponseProcessingEngine($responseProcessing);
	    $engine->setContext($state);
	    $engine->process();
	    $this->assertEquals(1, $state['OUTCOME']->getValue());
	}
}