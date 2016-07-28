<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\runtime\rules\RuleProcessingException;
use qtism\runtime\common\ProcessingException;
use qtism\runtime\common\State;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\processing\ResponseProcessingEngine;
use qtism\common\enums\Cardinality;
use qtism\common\enums\BaseType;

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
		$context['RESPONSE'] = new QtiIdentifier('ChoiceA');
		$engine->process();
		$this->assertInstanceOf(QtiFloat::class, $context['SCORE']);
		$this->assertEquals(1.0, $context['SCORE']->getValue());
        
        // --> answer as an incorrect response.
		$context['RESPONSE'] = new QtiIdentifier('ChoiceB');
		$engine->process();
		$this->assertInstanceOf(QtiFloat::class, $context['SCORE']);
		$this->assertEquals(0.0, $context['SCORE']->getValue());
	}
    
    public function testResponseProcessingNoResponseRule() {
		$responseProcessing = $this->createComponentFromXml('
			<responseProcessing>
                <responseCondition>
                    <responseIf>
                        <match>
                            <variable identifier="RESPONSE"/>
                            <baseValue baseType="identifier">ChoiceA</baseValue>
                        </match>
                        <!-- Do nothing... -->
                    </responseIf>
                    <responseElseIf>
                        <match>
                            <variable identifier="RESPONSE"/>
                            <baseValue baseType="identifier">ChoiceB</baseValue>
                        </match>
                        <!-- Do nothing... -->
                    </responseElseIf>
                    <responseElse>
                        <setOutcomeValue identifier="SCORE">
                            <baseValue baseType="float">1.0</baseValue>
                        </setOutcomeValue>
                    </responseElse>
                </responseCondition>
            </responseProcessing>
		');
		
		$responseDeclaration = $this->createComponentFromXml('
			<responseDeclaration identifier="RESPONSE" cardinality="single" baseType="identifier"/>	
		');
		
		$outcomeDeclaration = $this->createComponentFromXml('
			<outcomeDeclaration identifier="SCORE" cardinality="single" baseType="float"/>
		');
		
		$respVar = ResponseVariable::createFromDataModel($responseDeclaration);
		$outVar = OutcomeVariable::createFromDataModel($outcomeDeclaration);
		$context = new State(array($respVar, $outVar));
		
		$engine = new ResponseProcessingEngine($responseProcessing, $context);
		
		$context['RESPONSE'] = new QtiIdentifier('ChoiceA');
		$engine->process();
		$this->assertNull($context['SCORE']);
        
        $context['RESPONSE'] = new QtiIdentifier('ChoiceB');
		$engine->process();
		$this->assertNull($context['SCORE']);
        
        $context['RESPONSE'] = new QtiIdentifier('ChoiceC');
        $engine->process();
        $this->assertInstanceOf('qtism\\common\\datatypes\\QtiFloat', $context['SCORE']);
		$this->assertEquals(1.0, $context['SCORE']->getValue());
	}
	
	public function testResponseProcessingExitResponse() {
	    $responseProcessing = $this->createComponentFromXml('
	        <responseProcessing>
                <exitResponse/>
	        </responseProcessing>
	    ');
	    
	    $engine = new ResponseProcessingEngine($responseProcessing);
	    
	    try {
	        $engine->process();
	        // An exception MUST be thrown.
	        $this->assertTrue(true);
	    }
	    catch (ProcessingException $e) {
	        $this->assertInstanceOf('qtism\\runtime\\rules\\RuleProcessingException', $e);
	        $this->assertEquals(RuleProcessingException::EXIT_RESPONSE, $e->getCode());
	    }
	}
    
    public function testSetOutcomeValueWithSum() {
        $responseProcessing = $this->createComponentFromXml('
            <responseProcessing>
                <responseCondition>
                  <responseIf>
                    <isNull>
                      <variable identifier="response-X" />
                    </isNull>
                    <setOutcomeValue identifier="score-X">
                      <baseValue baseType="integer">0</baseValue>
                    </setOutcomeValue>
                  </responseIf>
                  <responseElseIf>
                    <match>
                      <variable identifier="response-X" />
                      <correct identifier="response-X" />
                    </match>
                    <setOutcomeValue identifier="score-X">
                      <variable identifier="maxscore-X" />
                    </setOutcomeValue>
                 </responseElseIf>
                  <responseElse>
                    <setOutcomeValue identifier="score-X">
                      <baseValue baseType="integer">0</baseValue>
                    </setOutcomeValue>
                  </responseElse>
                </responseCondition>
                <setOutcomeValue identifier="SCORE">
                  <sum>
                    <variable identifier="score-X" />
                  </sum>
                </setOutcomeValue>
                <setOutcomeValue identifier="MAXSCORE">
                  <sum>
                    <variable identifier="maxscore-X" />
                  </sum>
                </setOutcomeValue>
              </responseProcessing>
        ');
        
        
        $responseX = new ResponseVariable('response-X', Cardinality::MULTIPLE, BaseType::IDENTIFIER);
        $responseX->setCorrectResponse(new MultipleContainer(BaseType::IDENTIFIER, array(new QtiIdentifier('ChoiceA'), new QtiIdentifier('ChoiceB'))));
        
        $score = new OutcomeVariable('SCORE', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.));
        $maxScore = new OutcomeVariable('MAXSCORE', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.));
        $scoreX = new OutcomeVariable('score-X', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(0.));
        $maxScoreX = new OutcomeVariable('maxscore-X', Cardinality::SINGLE, BaseType::FLOAT, new QtiFloat(1.));
        
        $state = new State(
            array(
                $responseX,
                $score,
                $maxScore,
                $scoreX,
                $maxScoreX
            )
        );
        
        // Try correct response...
        $state['response-X'][] = new QtiIdentifier('ChoiceA');
        $state['response-X'][] = new QtiIdentifier('ChoiceB');
        
        $engine = new ResponseProcessingEngine($responseProcessing, $state);
        $engine->process();
        
        $this->assertEquals(1., $state['score-X']->getValue());
        $this->assertEquals(1., $state['SCORE']->getValue());
        $this->assertEquals(1., $state['MAXSCORE']->getValue());
    }
}
