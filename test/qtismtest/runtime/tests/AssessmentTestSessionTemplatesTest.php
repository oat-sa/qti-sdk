<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\common\datatypes\Identifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\AssessmentTestSessionState;

class AssessmentTestSessionTemplatesTest extends QtiSmAssessmentTestSessionTestCase {
	
    public function testSimpleTemplating() {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/templates/template_test_simple.xml');
        $session->beginTestSession();
        // We are in linear mode with no branching/preconditions, so the sessions are alive...
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        
        // QTPL1 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))              
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        // QTPL2 - correct response.
        $session->beginAttempt();
        $responses = new State(
            array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceB')))
        );
        $session->endAttempt($responses);
        $session->moveNext();
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
        $this->assertEquals(2.0, $session['QTPL2.SCORE']->getValue());
    }
}