<?php
namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentTestSessionState;

use qtism\common\datatypes\QtiBoolean;
use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;

/**
 * Focus on testing the numberCompleted method of AssessmentTestSession.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class AssessmentTestSessionEndAttemptInteractionTest extends QtiSmAssessmentTestSessionTestCase {
    
    public function testEndAttemptInteraction() {
        
        // Max Attempts = 0 -> Infinite.
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/endAttemptIdentifiers.xml');
        $session->beginTestSession();
        
        // -- Item Q01 - Contains a ResponseVariable 'HINT' related to an endAttemptInteraction.
        $session->beginAttempt();
        
        // At the beginning of the attempt, all ResponseVariables related to an endAttemptInteraction
        // must be reset to false, ignoring default values (in this case: null).
        $this->assertFalse($session['Q01.HINT']->getValue());
        
        // SHOWHINT is not related to an endAttemptInteraction. Its default value is false.
        $this->assertFalse($session['Q01.SHOWHINT']->getValue());
        
        // End of attempt using endAttemptInteraction 'HINT'.
        $session->endAttempt(new State(array(new ResponseVariable('HINT', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)))));
        
        $this->assertTrue($session['Q01.HINT']->getValue());
        $this->assertTrue($session['Q01.SHOWHINT']->getValue());
        $this->assertEquals(0.0, $session['Q01.SCORE']->getValue());
        
        // -- Item Q01 - Second attempt. With the 'HINT', the candidate is able to give a correct response ;)!
        $session->beginAttempt();
        
        // Again, at the beginning of the attempt, all ResponseVariables bound to an endAttemptInteraction
        // are reset to false, ignoring their default values.
        $this->assertFalse($session['Q01.HINT']->getValue());
        $this->assertTrue($session['Q01.SHOWHINT']->getValue());
        
        $session->endAttempt(
            new State(
                array(
                    new ResponseVariable('HINT', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)),
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))
                )
            )
        );
        
        $this->assertFalse($session['Q01.HINT']->getValue());
        $this->assertFalse($session['Q01.SHOWHINT']->getValue());
        $this->assertEquals(1.0, $session['Q01.SCORE']->getValue());
        
        $session->moveNext();
        
        // Q02 - No endAttemptInteraction.
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB')))));
        
        $this->assertEquals(1.0, $session['Q02.SCORE']->getValue());
        
        $session->moveNext();
        
        // Q03 - Contains two ResponseVariables related to endAttemptInteraction ('HINT1', 'HINT2').
        $session->beginAttempt();
        
        // At the beginning of the attempt, all ResponseVariables related to and endAttemptInteraction
        // must be reset to false, ignoring default values (in this case: null).
        $this->assertFalse($session['Q03.HINT1']->getValue());
        $this->assertFalse($session['Q03.HINT2']->getValue());
        
        // The candidate ends the attempt by invoking the 'HINT1' endAttemptInteraction.
        $session->endAttempt(
            new State(
                array(
                    new ResponseVariable('HINT1', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true)),
                    new ResponseVariable('HINT2', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false))
                )
            )
        );
        
        $this->assertTrue($session['Q03.HINT1']->getValue());
        $this->assertFalse($session['Q03.HINT2']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT1']->getValue());
        $this->assertFalse($session['Q03.SHOWHINT2']->getValue());
        $this->assertEquals(0.0, $session['Q03.SCORE']->getValue());
        
        // -- Q03 - New attempt.
        // Unfortunately, HINT1 is not enough for the candidate to find the correct response ;) !
        $session->beginAttempt();
        
        $this->assertFalse($session['Q03.HINT1']->getValue());
        $this->assertFalse($session['Q03.HINT2']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT1']->getValue());
        $this->assertFalse($session['Q03.SHOWHINT2']->getValue());
        
        $session->endAttempt(
            new State(
                array(
                    new ResponseVariable('HINT1', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)),
                    new ResponseVariable('HINT2', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)),
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceE'))
                )
            )
        );
        
        // -- Q03 - New attempt to ask for HINT 2.
        $session->beginAttempt();
        
        $this->assertFalse($session['Q03.HINT1']->getValue());
        $this->assertFalse($session['Q03.HINT2']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT1']->getValue());
        $this->assertFalse($session['Q03.SHOWHINT2']->getValue());
        
        $session->endAttempt(
            new State(
                array(
                    new ResponseVariable('HINT1', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)),
                    new ResponseVariable('HINT2', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true))
                )
            )
        );
        
        $this->assertFalse($session['Q03.HINT1']->getValue());
        $this->assertTrue($session['Q03.HINT2']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT1']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT2']->getValue());
        $this->assertEquals(0.0, $session['Q03.SCORE']->getValue());
        
        // -- Q03 - Candidate is now able to find the appropriate response.
        $session->beginAttempt();
        $session->endAttempt(new State(
            array(
                new ResponseVariable('HINT1', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)),
                new ResponseVariable('HINT2', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false)),
                new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))
            )
        ));
        
        $this->assertFalse($session['Q03.HINT1']->getValue());
        $this->assertFalse($session['Q03.HINT2']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT1']->getValue());
        $this->assertTrue($session['Q03.SHOWHINT2']->getValue());
        $this->assertEquals(1.0, $session['Q03.SCORE']->getValue());
        
        $session->moveNext();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }
}
