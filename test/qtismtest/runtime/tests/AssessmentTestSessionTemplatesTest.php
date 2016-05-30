<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSessionState;

class AssessmentTestSessionTemplatesTest extends QtiSmAssessmentTestSessionTestCase {
	
    public function testSimpleTemplatingLinear() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/templates/template_default_test_simple_linear.xml');
        $session->beginTestSession();
        // We are in linear mode with no branching/preconditions, so the sessions are instantiated after beginTestSession call.
        // However, the templateDefaults/templateProcessings will only occur at the beginning of the very first attempt.
        $this->assertNull($session['QTPL1.GOODSCORE']);
        $this->assertNull($session['QTPL1.WRONGSCORE']);
        $this->assertNull($session['QTPL2.GOODSCORE']);
        $this->assertNull($session['QTPL2.WRONGSCORE']);
        
        // QTPL1 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))              
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        // QTPL2 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.SCORE']->getValue());
    }
    
    public function testSimpleTemplatingNonLinear() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/templates/template_default_test_simple_nonlinear.xml');
        $session->beginTestSession();
        // We are in nonlinear mode, so the sessions are instantiated after beginTestSession call.
        // The templateDefaults/templateProcessings will occur at the beginning of the testPart.
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        
        // QTPL1 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))              
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        // QTPL2 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.SCORE']->getValue());
    }
    
    public function testSimpleTemplatingNonLinearMultipleTestParts() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/templates/template_default_test_simple_nonlinear_multiple_testparts.xml');
        $session->beginTestSession();
        // We are in nonlinear mode, so the sessions are instantiated after beginTestSession call.
        // The templateDefaults/templateProcessings will occur at the beginning of the testPart.
        
        // TestPart "P01" has begun...
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        
        // TestPart "P02" has not begun...
        $this->assertNull($session['QTPL3.GOODSCORE']);
        $this->assertNull($session['QTPL3.WRONGSCORE']);
        
        // QTPL1 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')))              
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        // QTPL2 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        // We just entered testPart "P02", it has now begun. Template Defaults have been applied!
        $this->assertEquals(3.0, $session['QTPL3.GOODSCORE']->getValue());
        $this->assertEquals(-2.0, $session['QTPL3.WRONGSCORE']->getValue());
        
        // QTPL3 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC')))
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.SCORE']->getValue());
        $this->assertEquals(3.0, $session['QTPL3.SCORE']->getValue());
    }
}
