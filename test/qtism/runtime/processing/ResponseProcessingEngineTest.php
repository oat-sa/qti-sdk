<?php
use qtism\runtime\common\State;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\processing\ResponseProcessingEngine;

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
		$context['RESPONSE'] = 'ChoiceA';
		$engine->process();
		$this->assertInternalType('float', $context['SCORE']);
		$this->assertEquals(1.0, $context['SCORE']);
		
		var_dump(''.$engine->getStackTrace());
	}
}