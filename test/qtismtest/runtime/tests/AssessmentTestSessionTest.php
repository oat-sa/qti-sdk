<?php

namespace qtismtest\runtime\tests;

use InvalidArgumentException;
use OutOfBoundsException;
use OutOfRangeException;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\datatypes\QtiString;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\NavigationMode;
use qtism\data\storage\php\PhpStorageException;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\data\storage\xml\XmlDocument;
use qtism\data\storage\xml\XmlStorageException;
use qtism\data\SubmissionMode;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\OutcomeVariable;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableIdentifier;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestPlace;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\OrderingException;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmAssessmentTestSessionTestCase;
use qtism\data\state\Weight;

/**
 * Class AssessmentTestSessionTest
 */
class AssessmentTestSessionTest extends QtiSmAssessmentTestSessionTestCase
{
    protected $state;

    public function setUp(): void
    {
        parent::setUp();

        $xml = new XmlCompactDocument('2.1');
        $xml->load(self::samplesDir() . 'custom/runtime/assessmenttest_context.xml');

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $this->state = $sessionManager->createAssessmentTestSession($xml->getDocumentComponent());
        $this->state['OUTCOME1'] = new QtiString('String!');
    }

    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->state);
    }

    /**
     * @return AssessmentTestSession
     */
    public function getState()
    {
        return $this->state;
    }

    public function testInstantiateOne()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $this->assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

        // You cannot get information on the current elements of
        // the test session when INITIAL state is in force.
        $this->assertFalse($assessmentTestSession->getCurrentAssessmentItemRef());
        $this->assertFalse($assessmentTestSession->getCurrentAssessmentSection());
        $this->assertFalse($assessmentTestSession->getCurrentNavigationMode());
        $this->assertFalse($assessmentTestSession->getCurrentSubmissionMode());
        $this->assertFalse($assessmentTestSession->getCurrentTestPart());
        $this->assertFalse($assessmentTestSession->getCurrentRemainingAttempts());

        $assessmentTestSession->beginTestSession();
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Now that the test session has begun, you can get information
        // about the current elements of the session.
        $this->assertEquals('P01', $assessmentTestSession->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('S01', $assessmentTestSession->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertIsInt($assessmentTestSession->getCurrentNavigationMode());
        $this->assertEquals(NavigationMode::LINEAR, $assessmentTestSession->getCurrentNavigationMode());
        $this->assertIsInt($assessmentTestSession->getCurrentSubmissionMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $assessmentTestSession->getCurrentSubmissionMode());
        $this->assertEquals(1, $assessmentTestSession->getCurrentRemainingAttempts());

        // test-level outcome variables should be initialized
        // with their default values.
        $this->assertInstanceOf(QtiFloat::class, $assessmentTestSession['SCORE']);
        $this->assertEquals(0.0, $assessmentTestSession['SCORE']->getValue());

        // No session ID should be set, this is the role of AssessmentTestSession Storage Services.
        $this->assertEquals('no_session_id', $assessmentTestSession->getSessionId());
    }

    public function testInstantiateTwo()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');
        $assessmentTestSession->beginTestSession();
        // check Q01.1, Q01.2, Q01.3 item sessions are all initialized.
        for ($i = 1; $i <= 3; $i++) {
            $score = $assessmentTestSession["Q01.${i}.SCORE"];
            $response = $assessmentTestSession["Q01.${i}.RESPONSE"];
            $this->assertInstanceOf(QtiFloat::class, $score);
            $this->assertEquals(0.0, $score->getValue());
            $this->assertSame(null, $response);
        }
    }

    public function testSetVariableValuesAfterInstantiationOne()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();

        // Change the value of the global SCORE.
        $this->assertEquals(0.0, $assessmentTestSession['SCORE']->getValue());
        $assessmentTestSession['SCORE'] = new QtiFloat(20.0);
        $this->assertEquals(20.0, $assessmentTestSession['SCORE']->getValue());

        // the assessment test session has no variable MAXSCORE.
        $this->assertSame(null, $assessmentTestSession['MAXSCORE']);
        try {
            $assessmentTestSession['MAXSCORE'] = new QtiFloat(20.0);
            // An exception must be thrown in this case!
            $this->assertTrue(false);
        } catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        // Change the value of Q01.SCORE.
        $this->assertEquals(0.0, $assessmentTestSession['Q01.SCORE']->getValue());
        $assessmentTestSession['Q01.SCORE'] = new QtiFloat(1.0);
        $this->assertEquals(1.0, $assessmentTestSession['Q01.SCORE']->getValue());

        // Q01 has no 'MAXSCORE' variable.
        $this->assertSame(null, $assessmentTestSession['Q01.MAXSCORE']);
        try {
            $assessmentTestSession['Q01.MAXSCORE'] = new QtiFloat(1.0);
            // An exception must be thrown !
            $this->assertTrue(false);
        } catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }

        // No item Q04.
        $this->assertSame(null, $assessmentTestSession['Q04.SCORE']);
        try {
            $assessmentTestSession['Q04.SCORE'] = new QtiFloat(1.0);
            // Because no such item, outofbounds.
            $this->assertTrue(false);
        } catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
    }

    public function testLinearSkipAll()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();

        $this->assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
        $this->assertEquals('S01', $assessmentTestSession->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $assessmentTestSession->getCurrentTestPart()->getIdentifier());
        $this->assertFalse($assessmentTestSession->isCurrentAssessmentItemAdaptive());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(new State());
        $assessmentTestSession->moveNext();
        $this->assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
        $this->assertFalse($assessmentTestSession->isCurrentAssessmentItemAdaptive());

        $this->assertEquals(1, $assessmentTestSession->getCurrentRemainingAttempts());
        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(new State());
        $assessmentTestSession->moveNext();
        $this->assertEquals('Q03', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
        $this->assertFalse($assessmentTestSession->isCurrentAssessmentItemAdaptive());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(new State());
        $assessmentTestSession->moveNext();

        $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
        $this->assertFalse($assessmentTestSession->getCurrentAssessmentItemRef());
        $this->assertFalse($assessmentTestSession->getCurrentAssessmentSection());
        $this->assertFalse($assessmentTestSession->getCurrentTestPart());
        $this->assertFalse($assessmentTestSession->getCurrentNavigationMode());
        $this->assertFalse($assessmentTestSession->getCurrentSubmissionMode());
    }

    public function testLinearAnswerAll()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();

        // Q01 - Correct Response = 'ChoiceA'.
        $this->assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertFalse($assessmentTestSession->isCurrentAssessmentItemInteracting());
        $assessmentTestSession->beginAttempt();
        $this->assertTrue($assessmentTestSession->isCurrentAssessmentItemInteracting());
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')));
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();
        $this->assertFalse($assessmentTestSession->isCurrentAssessmentItemInteracting());

        // Q02 - Correct Response = 'ChoiceB'.
        $this->assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $assessmentTestSession->beginAttempt();
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))); // -> incorrect x)
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();

        // Q03 - Correct Response = 'ChoiceC'.
        $this->assertEquals('Q03', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $assessmentTestSession->beginAttempt();
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC')));
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();

        // Check the final state of the test session.
        // - Q01
        $this->assertEquals('ChoiceA', $assessmentTestSession['Q01.RESPONSE']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $assessmentTestSession['Q01.SCORE']);
        $this->assertEquals(1.0, $assessmentTestSession['Q01.SCORE']->getValue());
        $this->assertInstanceOf(QtiInteger::class, $assessmentTestSession['Q01.numAttempts']);
        $this->assertEquals(1, $assessmentTestSession['Q01.numAttempts']->getValue());

        // - Q02
        $this->assertEquals('ChoiceC', $assessmentTestSession['Q02.RESPONSE']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $assessmentTestSession['Q02.SCORE']);
        $this->assertEquals(0.0, $assessmentTestSession['Q02.SCORE']->getValue());
        $this->assertInstanceOf(QtiInteger::class, $assessmentTestSession['Q02.numAttempts']);
        $this->assertEquals(1, $assessmentTestSession['Q02.numAttempts']->getValue());

        // - Q03
        $this->assertEquals('ChoiceC', $assessmentTestSession['Q03.RESPONSE']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $assessmentTestSession['Q03.SCORE']);
        $this->assertEquals(1.0, $assessmentTestSession['Q03.SCORE']->getValue());
        $this->assertInstanceOf(QtiInteger::class, $assessmentTestSession['Q03.numAttempts']);
        $this->assertEquals(1, $assessmentTestSession['Q03.numAttempts']->getValue());

        $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
    }

    public function testLinearSimultaneousSubmission()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        $session->beginTestSession();

        // Q01 - Correct.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        // The session must be suspended until the end of the test part.
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());

        // !!! The Response must not be stored in the session. Indeed, it is not considered as "submitted" yet.
        // !!! No score must be computed, as the submission will occur at the end of the test part.
        // This is the same for the next items.
        $this->assertSame(null, $session['Q01.RESPONSE']);
        $this->assertEquals(0.0, $session['Q01.scoring']->getValue());

        // We must have an entry in pending responses. In addition, we should be able to
        // access the provided response through the candidate state.
        $this->assertCount(1, $session->getPendingResponses());

        $candidateState = $session->getCandidateState();
        $this->assertCount(3, $candidateState);
        $this->assertEquals('ChoiceA', $candidateState['RESPONSE']->getValue());
        $this->assertEquals(1, $candidateState['numAttempts']->getValue());
        $this->assertEquals('PT0S', $candidateState['duration']->__toString());

        $session->moveNext();

        // Q02 - Incorrect (but SCORE = 3)
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M')]))]));
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertSame(null, $session['Q02.RESPONSE']);
        $this->assertEquals(0.0, $session['Q02.SCORE']->getValue());
        $this->assertEquals(2, count($session->getPendingResponses()));
        $session->moveNext();

        // Q03 - Skip.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $session->moveNext();
        $this->assertEquals(3, count($session->getPendingResponses()));

        // Q04 - Skip.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertEquals(4, count($session->getPendingResponses()));
        $session->moveNext();

        // Q05 - Skip.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertEquals(5, count($session->getPendingResponses()));
        $session->moveNext();

        // Q06 - Skip.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertEquals(6, count($session->getPendingResponses()));
        $session->moveNext();

        // Q07.1 - Correct.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]));
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertSame(null, $session['Q07.1.RESPONSE']);
        $this->assertInstanceOf(QtiFloat::class, $session['Q07.1.SCORE']);
        $this->assertEquals(0.0, $session['Q07.1.SCORE']->getValue());
        $this->assertEquals(7, count($session->getPendingResponses()));
        $session->moveNext();

        // Q07.2 - Incorrect (but SCORE = 1).
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(103, 113))]));
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertSame(null, $session['Q07.2.RESPONSE']);
        $this->assertEquals(0.0, $session['Q07.2.SCORE']->getValue());
        $this->assertEquals(8, count($session->getPendingResponses()));
        $session->moveNext();

        // Q07.3 - Incorrect (and SCORE = 0).
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(50, 60))]));
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $this->assertSame(null, $session['Q07.3.RESPONSE']);
        $this->assertEquals(0.0, $session['Q07.3.SCORE']->getValue());

        // This triggers response processing for the test part in simultaneous mode.
        $session->moveNext();

        // This is the end of the test. Then, the pending responses were flushed.
        // We also have to check if the deffered response processing took place.
        $this->assertEquals(0, count($session->getPendingResponses()));

        $this->assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());

        $this->assertTrue($session['Q02.RESPONSE']->equals(new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M')])));
        $this->assertEquals(3.0, $session['Q02.SCORE']->getValue());

        $this->assertEquals(0.0, $session['Q03.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q04.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q05.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q06.mySc0r3']->getValue());

        $this->assertTrue($session['Q07.1.RESPONSE']->equals(new QtiPoint(102, 113)));
        $this->assertTrue($session['Q07.2.RESPONSE']->equals(new QtiPoint(103, 113)));
        $this->assertTrue($session['Q07.3.RESPONSE']->equals(new QtiPoint(50, 60)));

        // Did the test-level outcome processing take place?
        $this->assertEquals(9, $session['NPRESENTED']->getValue());

        // All item sessions now closed?
        foreach ($session->getAssessmentItemSessionStore()->getAllAssessmentItemSessions() as $itemSession) {
            $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        }
    }

    /**
     * @dataProvider linearOutcomeProcessingProvider
     *
     * @param array $responses
     * @param array $outcomes
     * @throws AssessmentItemSessionException
     * @throws AssessmentTestSessionException
     * @throws XmlStorageException
     * @throws PhpStorageException
     */
    public function testLinearOutcomeProcessing(array $responses, array $outcomes)
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $assessmentTestSession->beginTestSession();

        // There must be 8 outcome variables to be checked:
        // NCORRECTS01, NCORRECTS02, NCORRECTS03, NINCORRECT, NRESPONDED
        // NPRESENTED, NSELECTED, PERCENT_CORRECT.
        $this->assertEquals(array_keys($outcomes), ['NCORRECTS01', 'NCORRECTS02', 'NCORRECTS03', 'NINCORRECT', 'NRESPONSED', 'NPRESENTED', 'NSELECTED', 'PERCENT_CORRECT']);

        // The selection of items for the test is 9.
        $this->assertEquals(9, count($responses));

        foreach ($responses as $resp) {
            $assessmentTestSession->beginAttempt();
            $assessmentTestSession->endAttempt($resp);

            // Only 1 attempt allowed by item. It means the session closes after the first attempt is completed.
            $this->assertEquals(AssessmentItemSessionState::CLOSED, $assessmentTestSession->getCurrentAssessmentItemSession()->getState());

            $assessmentTestSession->moveNext();
        }

        $this->assertFalse($assessmentTestSession->isRunning());
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());

        foreach ($outcomes as $outcomeIdentifier => $outcomeValue) {
            $this->assertInstanceOf(($outcomeValue instanceof QtiInteger) ? QtiInteger::class : QtiFloat::class, $assessmentTestSession[$outcomeIdentifier]);

            if ($outcomeIdentifier !== 'PERCENT_CORRECT') {
                $this->assertEquals($outcomeValue->getValue(), $assessmentTestSession[$outcomeIdentifier]->getValue());
            } else {
                $this->assertEquals(round($outcomeValue->getValue(), 2), round($assessmentTestSession[$outcomeIdentifier]->getValue(), 2));
            }
        }
    }

    /**
     * @return array
     */
    public function linearOutcomeProcessingProvider()
    {
        $returnValue = [];

        // Test 1.
        $outcomes = [
            'NCORRECTS01' => new QtiInteger(2),
            'NCORRECTS02' => new QtiInteger(1),
            'NCORRECTS03' => new QtiInteger(1),
            'NINCORRECT' => new QtiInteger(5),
            'NRESPONSED' => new QtiInteger(9),
            'NPRESENTED' => new QtiInteger(9),
            'NSELECTED' => new QtiInteger(9),
            'PERCENT_CORRECT' => new QtiFloat(44.44),
        ];
        $responses = [];
        $responses['Q01'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]); // SCORE = 1 - Correct
        $responses['Q02'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('D', 'L')]))]); // SCORE = 3 - Incorrect
        $responses['Q03'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('H'), new QtiIdentifier('O')]))]); // SCORE = 2 - Correct
        $responses['Q04'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('W', 'Sp'), new QtiDirectedPair('G2', 'Su')]))]); // SCORE = 0 - Incorrect
        $responses['Q05'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('C', 'B'), new QtiPair('C', 'D'), new QtiPair('B', 'D')]))]); // SCORE = 1 - Incorrect
        $responses['Q06'] = new State([new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A'))]); // SCORE = 1 - Correct
        $responses['Q07.1'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(105, 105))]); // SCORE = 1 - Incorrect
        $responses['Q07.2'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]); // SCORE = 1 - Correct
        $responses['Q07.3'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(13, 37))]); // SCORE = 0 - Incorrect

        $test = [$responses, $outcomes];
        $returnValue[] = $test;

        // Test 2 (full correct).
        $outcomes = [
            'NCORRECTS01' => new QtiInteger(3),
            'NCORRECTS02' => new QtiInteger(3),
            'NCORRECTS03' => new QtiInteger(3),
            'NINCORRECT' => new QtiInteger(0),
            'NRESPONSED' => new QtiInteger(9),
            'NPRESENTED' => new QtiInteger(9),
            'NSELECTED' => new QtiInteger(9),
            'PERCENT_CORRECT' => new QtiFloat(100.00),
        ];
        $responses = [];
        $responses['Q01'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]); // SCORE = 1 - Correct
        $responses['Q02'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M'), new QtiPair('D', 'L')]))]); // SCORE = 4 - Correct
        $responses['Q03'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('H'), new QtiIdentifier('O')]))]); // SCORE = 2 - Correct
        $responses['Q04'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('W', 'G1'), new QtiDirectedPair('Su', 'G2')]))]); // SCORE = 3 - Correct
        $responses['Q05'] = new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('C', 'B'), new QtiPair('C', 'D')]))]); // SCORE = 2 - Correct
        $responses['Q06'] = new State([new ResponseVariable('answer', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('A'))]); // SCORE = 1 - Correct
        $responses['Q07.1'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]); // SCORE = 1 - Correct
        $responses['Q07.2'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]); // SCORE = 1 - Correct
        $responses['Q07.3'] = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]); // SCORE = 0 - Correct

        $test = [$responses, $outcomes];
        $returnValue[] = $test;

        return $returnValue;
    }

    public function testWichLastOccurenceUpdate()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');
        $assessmentTestSession->beginTestSession();

        $this->assertFalse($assessmentTestSession->whichLastOccurenceUpdate($assessmentTestSession->getAssessmentTest()->getComponentByIdentifier('Q01')));

        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();

        $this->assertEquals(0, $assessmentTestSession->whichLastOccurenceUpdate('Q01'));

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(new State());
        $assessmentTestSession->moveNext();
        $this->assertEquals(1, $assessmentTestSession->whichLastOccurenceUpdate('Q01'));

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();
        $this->assertEquals(2, $assessmentTestSession->whichLastOccurenceUpdate('Q01'));
    }

    public function testGetAssessmentItemSessions()
    {
        // --- Test with single occurence items.
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();

        foreach (['Q01', 'Q02', 'Q03'] as $identifier) {
            $assessmentTestSession->beginAttempt();
            $assessmentTestSession->endAttempt(new State());
            $assessmentTestSession->moveNext();
            $sessions = $assessmentTestSession->getAssessmentItemSessions($identifier);
            $this->assertEquals(1, count($sessions));
            $this->assertEquals($identifier, $sessions[0]->getAssessmentItem()->getIdentifier());
        }

        // Malformed $identifier.
        try {
            $sessions = $assessmentTestSession->getAssessmentItemSessions('Q04.1');
            $this->assertFalse(true);
        } catch (InvalidArgumentException $e) {
            $this->assertTrue(true);
        }

        // Unknown assessmentItemRef.
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));

        // --- Test with multiple occurence items.
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        for ($i = 0; $i < 3; $i++) {
            $assessmentTestSession->beginAttempt();
            $assessmentTestSession->endAttempt(new State());
            $assessmentTestSession->moveNext();
        }

        $sessions = $assessmentTestSession->getAssessmentItemSessions('Q01');
        $this->assertEquals(3, count($sessions));
        for ($i = 0; $i < count($sessions); $i++) {
            $this->assertEquals('Q01', $sessions[$i]->getAssessmentItem()->getIdentifier());
        }
    }

    public function testPossibleJumpsTestPart()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/jumps.xml');

        // The session has not begun, the candidate is not able to jump anywhere.
        $this->assertEquals(0, count($session->getPossibleJumps(false)));

        $session->beginTestSession();
        $jumps = $session->getPossibleJumps(AssessmentTestPlace::TEST_PART);
        $this->assertEquals(6, count($jumps));
        $this->assertEquals('Q01', $jumps[0]->getTarget()->getAssessmentItemRef()->getIdentifier('Q01'));
        $this->assertEquals(0, $jumps[0]->getPosition());
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $jumps[0]->getItemSession()->getState());
        $this->assertEquals('Q02', $jumps[1]->getTarget()->getAssessmentItemRef()->getIdentifier('Q02'));
        $this->assertEquals(1, $jumps[1]->getPosition());
        $this->assertEquals('Q03', $jumps[2]->getTarget()->getAssessmentItemRef()->getIdentifier('Q03'));
        $this->assertEquals(2, $jumps[2]->getPosition());
        $this->assertEquals('Q04', $jumps[3]->getTarget()->getAssessmentItemRef()->getIdentifier('Q04'));
        $this->assertEquals(3, $jumps[3]->getPosition());
        $this->assertEquals('Q05', $jumps[4]->getTarget()->getAssessmentItemRef()->getIdentifier('Q05'));
        $this->assertEquals(4, $jumps[4]->getPosition());
        $this->assertEquals('Q06', $jumps[5]->getTarget()->getAssessmentItemRef()->getIdentifier('Q06'));
        $this->assertEquals(5, $jumps[5]->getPosition());

        // The session has begun, the candidate is able to jump anywhere in testPart 'P01'.
        for ($i = 0; $i < 6; $i++) {
            $session->beginAttempt();
            $session->endAttempt(new State());
            $session->moveNext();
        }

        // We should be now in testPart 'PO2'.
        $this->assertEquals('P02', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        $jumps = $session->getPossibleJumps(AssessmentTestPlace::TEST_PART);
        $this->assertEquals(3, count($jumps));
        $this->assertEquals('Q07', $jumps[0]->getTarget()->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(6, $jumps[0]->getPosition());
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $jumps[0]->getItemSession()->getState());
        $this->assertEquals(0, $jumps[0]->getTarget()->getOccurence());
        $this->assertEquals('Q07', $jumps[1]->getTarget()->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(7, $jumps[1]->getPosition());
        $this->assertEquals(1, $jumps[1]->getTarget()->getOccurence());
        $this->assertEquals('Q07', $jumps[2]->getTarget()->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(8, $jumps[2]->getPosition());
        $this->assertEquals(2, $jumps[2]->getTarget()->getOccurence());

        for ($i = 0; $i < 3; $i++) {
            $session->beginAttempt();
            $session->endAttempt(new State());
            $session->moveNext();
        }

        // This is the end of the test session so no more possible jumps.
        $this->assertEquals(0, count($session->getPossibleJumps(false)));
    }

    public function testPossibleJumpsWholeTest()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/routeitem_position.xml');
        $session->beginTestSession();

        $jumps = $session->getPossibleJumps();
        $this->assertEquals(12, count($jumps));
    }

    public function testJumps()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/jumps.xml');
        $session->beginTestSession();

        // Begin attempt at Q01.
        $session->beginAttempt();

        // Moving to Q03 and answer it.
        $session->jumpTo(2);

        // Let's check that Q01 is in SUSPENDED state.
        $q01s = $session->getAssessmentItemSessions('Q01');
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $q01s[0]->getState());

        $this->assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('H'), new QtiIdentifier('O')]))]));
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getCurrentAssessmentItemSession()->getState());
        $session->moveNext();

        $this->assertEquals(2.0, $session['Q03.SCORE']->getValue());

        // Come back at Q01.
        $session->jumpTo(0);
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());

        // Wwe are at Q02.
        $this->assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P')]))]));
        $session->moveNext();
        $this->assertEquals(2.0, $session['Q02.SCORE']->getValue());

        // We are at Q03.
        $this->assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        try {
            $session->beginAttempt();
            // Only a single attempt is allowed.
            $this->assertFalse(true, 'Only a single attempt is allowed for Q03.');
        } catch (AssessmentTestSessionException $e) {
            // The assessment item session is closed.
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_ATTEMPTS_OVERFLOW, $e->getCode());
        }

        // Move to Q07.2
        $session->jumpTo(7);
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 102))]));
        $session->moveNext();
        $this->assertEquals(1.0, $session['Q07.2.SCORE']->getValue());

        // Q07.3
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // End of test, everything ok?
        $this->assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
        $this->assertInstanceOf(QtiFloat::class, $session['Q02.SCORE']);
        $this->assertInstanceOf(QtiFloat::class, $session['Q03.SCORE']);
        $this->assertInstanceOf(QtiFloat::class, $session['Q04.SCORE']); // Because auto forward = true, Q04 was selected as eligible after Q03's endAttempt. However, it was never attempted.
        $this->assertSame(0.0, $session['Q05.SCORE']->getValue());
        $this->assertSame(0.0, $session['Q06.mySc0r3']->getValue());
        $this->assertSame(0.0, $session['Q07.1.SCORE']->getValue());
        $this->assertInstanceOf(QtiFloat::class, $session['Q07.2.SCORE']);
        $this->assertInstanceOf(QtiFloat::class, $session['Q07.3.SCORE']);

        $this->assertEquals(5, $session['NPRESENTED']->getValue());
        $this->assertEquals(9, $session['NSELECTED']->getValue());
    }

    public function testJumpsSimultaneous()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/jumps_simultaneous.xml');
        $session->beginTestSession();

        // Begin attempt at Q01.
        $session->beginAttempt();

        // Moving to Q03 and answer it.
        $session->jumpTo(2);
        $this->assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('H'), new QtiIdentifier('O')]))]));
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());
        $session->moveNext();

        // Come back at Q01.
        $session->jumpTo(0);
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        // We are at Q02.
        $this->assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P')]))]));
        $session->moveNext();

        // We are at Q03.
        $this->assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());

        // Remember that in simulatenous submission mode, beginning an second attempt
        // will not raise an exception. Indeed, in this context, beginAttempt carry on
        // the current attempt.
        $this->assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        // Go back in testPart P01 to complete it. Q04, Q05 and Q06 must be responsed.
        $session->jumpTo(3);
        // Q04
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q05
        $this->assertEquals('Q05', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q06
        $this->assertEquals('Q06', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q07.1
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        // Jump to Q07.3
        $session->jumpTo(8);
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());

        // Jump to Q07.1
        $session->jumpTo(6);
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q07.2
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q07.3 already answered.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Outcome processing has now taken place. Everything OK?
        $this->assertEquals(2.0, $session['Q03.SCORE']->getValue());
        $this->assertEquals(2.0, $session['Q02.SCORE']->getValue());
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());
        $this->assertEquals(0.0, $session['Q04.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q05.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q06.mySc0r3']->getValue());
        $this->assertEquals(0.0, $session['Q07.1.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q07.2.SCORE']->getValue());
        $this->assertEquals(0.0, $session['Q07.3.SCORE']->getValue());

        $this->assertEquals(9, $session['NSELECTED']->getValue());
        $this->assertEquals(9, $session['NPRESENTED']->getValue());
    }

    public function testMoveNextAndBackNonLinearIndividual()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/itemsubset_nonlinear.xml');
        $session->beginTestSession();

        $this->assertEquals(NavigationMode::NONLINEAR, $session->getCurrentNavigationMode());
        $this->assertEquals(SubmissionMode::INDIVIDUAL, $session->getCurrentSubmissionMode());

        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->moveNext();
        $this->assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->moveBack();
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());

        try {
            // We are at the very first route item and want to move back... ouch!
            $session->moveBack();
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::LOGIC_ERROR, $e->getCode());
        }

        // We should still be on Q01.
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext(); // Q02
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q03
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q04
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q05
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q06
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q07.1
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q07.2
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();// Q07.3

        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // OutcomeProcessing?
        $this->assertInstanceOf(QtiFloat::class, $session['PERCENT_CORRECT']);
        $this->assertEquals(0.0, $session['PERCENT_CORRECT']->getValue());
        $this->assertEquals(9, $session['NSELECTED']->getValue());
    }

    public function testMoveNextAndBackNonLinearSimultaneous()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/jumps_simultaneous.xml');
        $session->beginTestSession();

        $this->assertEquals(NavigationMode::NONLINEAR, $session->getCurrentNavigationMode());
        $this->assertEquals(SubmissionMode::SIMULTANEOUS, $session->getCurrentSubmissionMode());

        // Q01.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        // Q02.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P')]))]));
        $session->moveNext();

        // Q03.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('O')]))]));
        $session->moveNext();

        // Q04.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q05
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // Q06.
        // (no scores computed yet).
        $this->assertEquals(0.0, $session['Q01.scoring']->getValue());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // We are now in another test part and some scores were processed for test part P01.
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());
    }

    public function testUnlimitedAttempts()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/unlimited_attempts.xml');
        $session->beginTestSession();

        $this->assertEquals(-1, $session->getCurrentRemainingAttempts());
        $session->beginAttempt();
        $this->assertEquals(-1, $session->getCurrentRemainingAttempts());
        $session->endAttempt(new State());
        $this->assertEquals(-1, $session->getCurrentRemainingAttempts());

        $session->beginAttempt();
        $this->assertEquals(-1, $session->getCurrentRemainingAttempts());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this->assertEquals(-1, $session->getCurrentRemainingAttempts());

        $session->moveNext();
        $this->assertEquals(-1, $session->getCurrentRemainingAttempts());
    }

    public function testSuspendInteractItemSession()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/unlimited_attempts.xml');
        $session->beginTestSession();

        // Suspend an item session in interacting state by moving to the next item during an attempt.
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $session->getCurrentAssessmentItemSession()->getState());
        $session->beginAttempt();
        $this->assertEquals(AssessmentItemSessionState::INTERACTING, $session->getCurrentAssessmentItemSession()->getState());
        $previousItemSession = $session->getCurrentAssessmentItemSession();
        $session->moveNext();
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $previousItemSession->getState());

        // Try to re-enter interacting state.
        $previousItemSession = $session->getCurrentAssessmentItemSession();
        $session->moveBack(); // We did not interact, then it remains INITIAL...
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $previousItemSession->getState());
        $this->assertEquals(AssessmentItemSessionState::INTERACTING, $session->getCurrentAssessmentItemSession()->getState());

        // Finally answer the question :) !
        $responses = new State([new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceA'))]);
        $session->endAttempt($responses);
        $session->moveNext();
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());

        // Q02...
        $session->beginAttempt();
        $this->assertEquals(AssessmentItemSessionState::INTERACTING, $session->getCurrentAssessmentItemSession()->getState());
    }

    /**
     * @dataProvider getWeightProvider
     *
     * @param string $identifier
     * @param float $expectedValue
     */
    public function testGetWeight($identifier, $expectedValue)
    {
        $state = $this->getState();

        $v = new VariableIdentifier($identifier);
        $weight = $state->getWeight($v);
        $this->assertInstanceOf(Weight::class, $weight);
        $this->assertEquals($v->getVariableName(), $weight->getIdentifier());
        $this->assertEquals($expectedValue, $weight->getValue());
    }

    /**
     * @dataProvider getWeightNotFoundProvider
     *
     * @param string $identifier
     */
    public function testGetWeightNotFound($identifier)
    {
        $state = $this->getState();

        $weight = $state->getWeight($identifier);
        $this->assertIsBool($weight);
        $this->assertSame(false, $weight);
    }

    /**
     * @dataProvider getWeightMalformed
     *
     * @param string $identifier
     */
    public function testGetWeightMalformed($identifier)
    {
        $state = $this->getState();
        $this->expectException(InvalidArgumentException::class);
        $state->getWeight($identifier);
    }

    /**
     * @return array
     */
    public function getWeightProvider()
    {
        return [
            ['Q01.W01', 1.0],
            ['Q01.W02', 1.1],
            ['W01', 1.0],
            ['W02', 1.1],
        ];
    }

    /**
     * @return array
     */
    public function getWeightNotFoundProvider()
    {
        return [
            ['Q01.W03'],
            ['Q02.W02'],
            ['Q01'],
            ['W04'],
        ];
    }

    /**
     * @return array
     */
    public function getWeightMalformed()
    {
        return [
            ['_Q01'],
            ['_Q01.SCORE'],
            ['Q04.1.W01'],
        ];
    }

    public function testRouteItemAssessmentSections()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/routeitem_assessmentsections.xml');
        $route = $assessmentTestSession->getRoute();

        // Route[0] - S01 -> S01A -> Q01
        $this->assertEquals('Q01', $route->getRouteItemAt(0)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(0)->getAssessmentSections();
        $this->assertEquals(2, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertTrue(isset($assessmentSections['S01A']));
        // The returned assessment section must be the nearest parent section.
        $this->assertEquals('S01A', $route->getRouteItemAt(0)->getAssessmentSection()->getIdentifier());

        // Route[1] - S01 -> S01A -> Q02
        $this->assertEquals('Q02', $route->getRouteItemAt(1)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(1)->getAssessmentSections();
        $this->assertEquals(2, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertTrue(isset($assessmentSections['S01A']));

        // Check for the order (from to to bottom of the hierarchy)
        $this->assertEquals(['S01', 'S01A'], $assessmentSections->getKeys());
        $this->assertEquals('S01A', $route->getRouteItemAt(1)->getAssessmentSection()->getIdentifier());

        // Route[2] - S01 -> S01A -> Q03
        $this->assertEquals('Q03', $route->getRouteItemAt(2)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(2)->getAssessmentSections();
        $this->assertEquals(2, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertTrue(isset($assessmentSections['S01A']));
        $this->assertEquals('S01A', $route->getRouteItemAt(0)->getAssessmentSection()->getIdentifier());

        // Route[3] - S01 -> S01B -> Q04
        $this->assertEquals('Q04', $route->getRouteItemAt(3)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(3)->getAssessmentSections();
        $this->assertEquals(2, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertTrue(isset($assessmentSections['S01B']));
        $this->assertEquals('S01B', $route->getRouteItemAt(3)->getAssessmentSection()->getIdentifier());

        // Route[4] - S01 -> S01B -> Q05
        $this->assertEquals('Q05', $route->getRouteItemAt(4)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(4)->getAssessmentSections();
        $this->assertEquals(2, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertTrue(isset($assessmentSections['S01B']));
        $this->assertEquals('S01B', $route->getRouteItemAt(4)->getAssessmentSection()->getIdentifier());

        // Route[5] - S01 -> S01B -> Q06
        $this->assertEquals('Q06', $route->getRouteItemAt(5)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(5)->getAssessmentSections();
        $this->assertEquals(2, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S01']));
        $this->assertTrue(isset($assessmentSections['S01B']));
        $this->assertEquals('S01B', $route->getRouteItemAt(5)->getAssessmentSection()->getIdentifier());

        // Route[6] - S02 -> Q07
        $this->assertEquals('Q07', $route->getRouteItemAt(6)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(6)->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S02']));
        $this->assertEquals('S02', $route->getRouteItemAt(6)->getAssessmentSection()->getIdentifier());

        // Route[7] - S02 -> Q08
        $this->assertEquals('Q08', $route->getRouteItemAt(7)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(7)->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S02']));
        $this->assertEquals('S02', $route->getRouteItemAt(7)->getAssessmentSection()->getIdentifier());

        // Route[8] - S02 -> Q09
        $this->assertEquals('Q09', $route->getRouteItemAt(8)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(8)->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S02']));
        $this->assertEquals('S02', $route->getRouteItemAt(8)->getAssessmentSection()->getIdentifier());

        // Route[9] - S03 -> Q10
        $this->assertEquals('Q10', $route->getRouteItemAt(9)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(9)->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S03']));
        $this->assertEquals('S03', $route->getRouteItemAt(9)->getAssessmentSection()->getIdentifier());

        // Route[10] - S03 -> Q11
        $this->assertEquals('Q11', $route->getRouteItemAt(10)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(10)->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S03']));
        $this->assertEquals('S03', $route->getRouteItemAt(10)->getAssessmentSection()->getIdentifier());

        // Route[11] - S03 -> Q12
        $this->assertEquals('Q12', $route->getRouteItemAt(11)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(11)->getAssessmentSections();
        $this->assertEquals(1, count($assessmentSections));
        $this->assertTrue(isset($assessmentSections['S03']));
        $this->assertEquals('S03', $route->getRouteItemAt(11)->getAssessmentSection()->getIdentifier());

        // Make sure that the assessmentSections are provided in the right order.
        // For instance, the correct order for route[0] is [S01, S01A].
        $order = ['S01', 'S01A'];
        $sections = $route->getRouteItemAt(0)->getAssessmentSections();
        $this->assertEquals(count($order), count($sections));
        $i = 0;

        $sections->rewind();
        while ($sections->valid()) {
            $current = $sections->current();
            $this->assertEquals($order[$i], $current->getIdentifier());
            $i++;
            $sections->next();
        }
    }

    public function testGetItemSessionControl()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/routeitem_itemsessioncontrols.xml');
        $route = $assessmentTestSession->getRoute();

        // Q01 - Must be under control of its own itemSessionControl.
        $control = $route->getRouteItemAt(0)->getItemSessionControl();
        $this->assertEquals(2, $control->getItemSessionControl()->getMaxAttempts());
        $this->assertTrue($assessmentTestSession->getAssessmentTest()->getComponentByIdentifier('Q01') === $control->getOwner());

        // Q07 - Must be under control of the ItemSessionControl of the parent AssessmentSection.
        $control = $route->getRouteItemAt(6)->getItemSessionControl();
        $this->assertEquals(3, $control->getItemSessionControl()->getMaxAttempts());
        $this->assertTrue($assessmentTestSession->getAssessmentTest()->getComponentByIdentifier('S02') === $control->getOwner());

        // Q10 - Is under no control.
        $control = $route->getRouteItemAt(9)->getItemSessionControl();
        $this->assertSame(null, $control);

        // Q13 - Must be under control of the ItemSessionControl of the parent TestPart.
        $control = $route->getRouteItemAt(12)->getItemSessionControl();
        $this->assertEquals(4, $control->getItemSessionControl()->getMaxAttempts());
        $this->assertTrue($assessmentTestSession->getAssessmentTest()->getComponentByIdentifier('P02') === $control->getOwner());
    }

    public function testGetTimeLimits()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/routeitem_timelimits.xml');
        $route = $assessmentTestSession->getRoute();

        // Q01
        $timeLimits = $route->getRouteItemAt(0)->getTimeLimits();
        $this->assertEquals(3, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(50, $timeLimits[2]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q02
        $timeLimits = $route->getRouteItemAt(1)->getTimeLimits();
        $this->assertEquals(2, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q08
        $timeLimits = $route->getRouteItemAt(7)->getTimeLimits();
        $this->assertEquals(3, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(150, $timeLimits[2]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q12
        $timeLimits = $route->getRouteItemAt(11)->getTimeLimits();
        $this->assertEquals(2, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q13
        $timeLimits = $route->getRouteItemAt(12)->getTimeLimits();
        $this->assertEquals(2, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(200, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q14
        $timeLimits = $route->getRouteItemAt(13)->getTimeLimits();
        $this->assertEquals(1, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Test item's timelimits exclusion.
        // Q01
        $timeLimits = $route->getRouteItemAt(0)->getTimeLimits(true);
        $this->assertEquals(2, count($timeLimits));
        $this->assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this->assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));
    }

    public function testRubricBlockRefsHierarchy()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/rubricblockrefs_hierarchy.xml', true);
        $route = $session->getRoute();

        // S01 - S01A - Q01
        $rubricBlockRefs = $route->getRouteItemAt(0)->getRubricBlockRefs();
        $this->assertEquals(['RB00_MAIN', 'RB01_MATH', 'RB02_MATH'], $rubricBlockRefs->getKeys());

        // S01 - S01A - Q02
        $rubricBlockRefs = $route->getRouteItemAt(1)->getRubricBlockRefs();
        $this->assertEquals(['RB00_MAIN', 'RB01_MATH', 'RB02_MATH'], $rubricBlockRefs->getKeys());

        // S01 - S01B - Q03
        $rubricBlockRefs = $route->getRouteItemAt(2)->getRubricBlockRefs();
        $this->assertEquals(['RB00_MAIN', 'RB03_BIOLOGY'], $rubricBlockRefs->getKeys());

        // S01C - Q04
        $rubricBlockRefs = $route->getRouteItemAt(3)->getRubricBlockRefs();
        $this->assertEquals(0, count($rubricBlockRefs));
    }

    public function testRouteItemPosition()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/routeitem_position.xml');
        $route = $session->getRoute();

        // Q01 - position 0.
        $routeItem = $route->getRouteItemAt(0);
        $this->assertEquals('Q01', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $route->getRouteItemPosition($routeItem));

        // Q02 - position 1.
        $routeItem = $route->getRouteItemAt(1);
        $this->assertEquals('Q02', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $route->getRouteItemPosition($routeItem));

        // ...

        // Q12 - position 11.
        $routeItem = $route->getRouteItemAt(11);
        $this->assertEquals('Q12', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(11, $route->getRouteItemPosition($routeItem));
    }

    public function testEmptySection()
    {
        // Aims at testing that even a section of the test is empty,
        // it is simply ignored at runtime.
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/empty_section.xml');
        $session->beginTestSession();

        // First section contains a single item.
        $this->assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        // The second section is empty, moveNext() goes to the end of the current route,
        // and the session is then closed.
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }

    public function testItemModalFeedbacks()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/item_modalfeedbacks/modalfeedbacks_nonadaptive_individual_linear.xml', true);
        $session->beginTestSession();

        // -- Q01 nonAdaptive, maxAttempts = 1, showFeedback = true.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('true'))]);
        $session->endAttempt($responses);
        // The ModalFeedback must be shown because even if the last attempt has been consumed, showFeedback is true.
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // -- Move from Q01 to Q02.
        $tempItemSession = $session->getCurrentAssessmentItemSession();
        $session->moveNext();

        // Just check that Q01 closed.
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $tempItemSession->getState());

        // -- Q02 nonAdaptive, maxAttempts = 0, showFeedback = false.
        // Here, the maxAttempts is 0 i.e. no limit. Moreover, feedback is shown only if the answer is wrong.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // Brutal new attempt, without suspending explicitely the item session!
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('true'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getCurrentAssessmentItemSession()->getState());

        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // -- Move from Q02 to Q03.
        $session->moveNext();

        // Make sure that Q02's session get's closed by moving next.
        $itemSessions = $session->getAssessmentItemSessions('Q02');
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $itemSessions[0]->getState());

        // -- Q03 nonAdaptive, maxAttempts = 2, showFeedback = false.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // itemSessionControl->showFeedback = false. It means that the last attempt will have no ModalFeedback shown.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getCurrentAssessmentItemSession()->getState());

        // -- Move from Q03 to Q04.
        $session->moveNext();

        // -- Q04 nonAdaptive, maxAttempts = 2, showFeedback = true
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // itemSessionControl->showFeedback = true. It means that the last attempt will have a ModalFeedback shown.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // -- Move from Q04 to Q05.
        $session->moveNext();
        // Check that Q04's session went to CLOSE state by moving next, because max number of attempts reached.
        $itemSessions = $session->getAssessmentItemSessions('Q04');
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSessions[0]->getState());

        // -- Q05 adaptive, showFeedback = true
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // $itemSessionControl->showFeedback = true, so the final "correct!" feedback is shown.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('true'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // -- Move from Q05 to Q06.
        $session->moveNext();
        // Check that Q05 went to CLOSE state.
        $itemSessions = $session->getAssessmentItemSessions('Q05');
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSessions[0]->getState());

        // -- Q06 adaptive, showFeedback = false
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('false'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::MODAL_FEEDBACK, $session->getCurrentAssessmentItemSession()->getState());

        // $itemSessionControl->showFeedback = false, so the "correct!" feedback is not shown.
        $session->beginAttempt();
        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('true'))]);
        $session->endAttempt($responses);
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getCurrentAssessmentItemSession()->getState());

        // Ends the test session.
        $session->moveNext();

        $itemSessions = $session->getAssessmentItemSessionStore()->getAllAssessmentItemSessions();
        foreach ($itemSessions as $itemSession) {
            $this->assertEquals(AssessmentItemSessionState::CLOSED, $itemSession->getState());
        }

        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }

    public function testIsTimeout()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/linear_5_items.xml');

        // If the session has not begun, the method systematically returns false.
        $this->assertFalse($session->isTimeout());

        // If no time limits in force, the test session is never considered timeout while running.
        $session->beginTestSession();
        $this->assertSame(0, $session->isTimeout());

        // Q01.
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $this->assertSame(0, $session->isTimeout());
        $session->moveNext();

        // Q02.
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this->assertSame(0, $session->isTimeout());
        $session->moveNext();

        // Q03.
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]));
        $this->assertSame(0, $session->isTimeout());
        $session->moveNext();

        // Q04.
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceD'))]));
        $this->assertSame(0, $session->isTimeout());
        $session->moveNext();

        // Q05.
        $session->beginAttempt();
        $this->assertSame(0, $session->isTimeout());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceE'))]));
        $this->assertSame(0, $session->isTimeout());
        $session->moveNext();

        // If the session is closed, the method systematically returns false.
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertFalse($session->isTimeout());
    }

    public function testGetRouteCountAllWithResponseDeclaration()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/route_count/all_with_responsedeclaration.xml');
        $session->beginTestSession();

        $this->assertEquals(3, $session->getRouteCount());
        $this->assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_ALL));
        $this->assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_EXCLUDENORESPONSE));
        $this->assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_FLOW));
    }

    public function testGetRouteCountMissingResponseDeclaration()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/route_count/missing_responsedeclaration.xml');
        $session->beginTestSession();

        $this->assertEquals(3, $session->getRouteCount());
        $this->assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_ALL));
        $this->assertEquals(2, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_EXCLUDENORESPONSE));
        $this->assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_FLOW));
    }

    public function testGetRouteCountMixed()
    {
        $qti = new XmlDocument();
        $qti->load(self::samplesDir() . 'custom/tests/linear_nonLinear_multiple_testparts/test.xml');
        $doc = XmlCompactDocument::createFromXmlAssessmentTestDocument($qti);
        $manager = new SessionManager(new FileSystemFileManager());
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $this->assertEquals(6, $session->getRouteCount());
        $this->assertEquals(6, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_ALL));
        $this->assertEquals(4, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_EXCLUDENORESPONSE));
        $this->assertEquals(5, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_FLOW));
    }

    public function testVisitedTestPartsLinear1TestPart()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/testparts/linear_1_testparts.xml');

        $this->assertFalse($session->isTestPartVisited('P01'));

        $session->beginTestSession();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertTrue($session->isTestPartVisited('P01'));
    }

    public function testVisitedTestPartsLinear2TestPart()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/testparts/linear_2_testparts.xml');

        $this->assertFalse($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));

        $session->beginTestSession();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));
    }

    public function testVisitedTestPartsNonLinear3TestPartJumpBeginningOfTestPart()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/testparts/nonlinear_3_testparts.xml');

        $this->assertFalse($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));

        $session->beginTestSession();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter P03 on Q07, which is the first item in P03.
        $session->jumpTo(6);

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertTrue($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter 03 on Q06, which is the last item in P02.
        $session->moveBack();
        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));
        $this->assertTrue($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));
    }

    public function testVisitedTestPartsNonLinear3TestPartJumpMiddleOfTestPart()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/testparts/nonlinear_3_testparts.xml');

        $this->assertFalse($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));

        $session->beginTestSession();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter P03 on Q08, which is the item in the middle of P03.
        $session->jumpTo(7);

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertTrue($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter 03 on Q05, which is the item in the middle of P02.
        $session->jumpTo(4);
        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));
        $this->assertTrue($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));
    }

    public function testVisitedTestPartsNonLinear3TestPartJumpEndOfTestPart()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/testparts/nonlinear_3_testparts.xml');

        $this->assertFalse($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));

        $session->beginTestSession();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertFalse($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter P03 on Q09, which is the item in the middle of P03.
        $session->jumpTo(8);

        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertFalse($session->isTestPartVisited('P02'));
        $this->assertTrue($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter 03 on Q04, which is the first item of P02.
        $session->jumpTo(3);
        $this->assertTrue($session->isTestPartVisited('P01'));
        $this->assertTrue($session->isTestPartVisited('P02'));
        $this->assertTrue($session->isTestPartVisited('P03'));
        $this->assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));
    }

    public function testGetFiles()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/files/files.xml');
        $fileManager = new FileSystemFileManager();
        $session->beginTestSession();

        // Q01.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, $fileManager->createFromData('response1', 'plain/text', 'file1.txt'))]));
        $session->moveNext();

        $files = $session->getFiles();
        $this->assertEquals(1, count($files));
        $this->assertEquals('file1.txt', $files[0]->getFileName());

        // Q02.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, $fileManager->createFromData('response2', 'plain/text', 'file2.txt'))]));
        $session->moveNext();

        $files = $session->getFiles();
        $this->assertEquals(2, count($files));
        $this->assertEquals('file1.txt', $files[0]->getFileName());
        $this->assertEquals('file2.txt', $files[1]->getFileName());

        // Q03.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, $fileManager->createFromData('response3', 'plain/text', 'file3.txt'))]));
        $session->moveNext();

        $files = $session->getFiles();
        $this->assertEquals(3, count($files));
        $this->assertEquals('file1.txt', $files[0]->getFileName());
        $this->assertEquals('file2.txt', $files[1]->getFileName());
        $this->assertEquals('file3.txt', $files[2]->getFileName());

        // Set a file value to the test level "TEST_FILE" outcome variable.
        $session['TEST_FILE'] = $fileManager->createFromData('testlevel', 'plain/text', 'filetest.txt');
        $files = $session->getFiles();
        $this->assertEquals(4, count($files));

        // Note: test level files always come first in the resulting array.
        $this->assertEquals('filetest.txt', $files[0]->getFileName());
        $this->assertEquals('file1.txt', $files[1]->getFileName());
        $this->assertEquals('file2.txt', $files[2]->getFileName());
        $this->assertEquals('file3.txt', $files[3]->getFileName());
    }

    public function testAllowSkipping()
    {
        $session = self::instantiate(self::samplesDir() . 'custom/runtime/skipping/skipping.xml');
        $session->beginTestSession();

        // !!! ALLOWSKIPPING = FALSE !!!

        // -- Q01.
        $session->beginAttempt();

        $this->assertNull($session['Q01.RESPONSE']);
        $this->assertEquals(1, $session['Q01.numAttempts']->getValue());

        // I should not be able to skip by providing an empty state.
        try {
            $session->endAttempt(new State());
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals("The Item Session 'Q01.0' is not allowed to be skipped.", $e->getMessage());

            // The session should not have changed.
            $this->assertNull($session['Q01.RESPONSE']);
            $this->assertEquals(1, $session['Q01.numAttempts']->getValue());
        }

        // I should not be able to skip by providing a null value for RESPONSE.
        try {
            $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER)]));
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals("The Item Session 'Q01.0' is not allowed to be skipped.", $e->getMessage());

            // The session should not have changed.
            $this->assertNull($session['Q01.RESPONSE']);
            $this->assertEquals(1, $session['Q01.numAttempts']->getValue());
        }

        // I should be able to end the attempt by providing a value for RESPONSE.
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $this->assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this->assertEquals(1, $session['Q01.numAttempts']->getValue());
        $this->assertEquals('completed', $session['Q01.completionStatus']->getValue());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q01')[0]->getState());

        $session->moveNext();

        // -- Q02.
        $session->beginAttempt();
        $this->assertEquals('ChoiceA', $session['Q02.RESPONSE']->getValue());
        $this->assertEquals(1, $session['Q02.numAttempts']->getValue());

        // I should not be able to skip by providing an empty state.
        try {
            $session->endAttempt(new State());
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals("The Item Session 'Q02.0' is not allowed to be skipped.", $e->getMessage());

            // The session should not have changed.
            $this->assertEquals('ChoiceA', $session['Q02.RESPONSE']->getValue());
            $this->assertEquals(1, $session['Q02.numAttempts']->getValue());
        }

        // I should be able to skip by providing a null value for RESPONSE (different from default).
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER)]));

        $session->beginAttempt();
        // I should not be able to skip by providing the 'ChoiceA' value for RESPONSE. Indeed, it's the RESPONSE's default value...
        try {
            $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals("The Item Session 'Q02.0' is not allowed to be skipped.", $e->getMessage());

            // The session should not have changed.
            $this->assertNull($session['Q02.RESPONSE']);
            $this->assertEquals(2, $session['Q02.numAttempts']->getValue());
        }

        // I should be able to end the attempt by providing a value different from the default value for the RESPONSE variable.
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this->assertEquals('ChoiceB', $session['Q02.RESPONSE']->getValue());
        $this->assertEquals(2, $session['Q02.numAttempts']->getValue());
        $this->assertEquals('completed', $session['Q02.completionStatus']->getValue());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q02')[0]->getState());

        $session->moveNext();

        // -- Q03.
        $session->beginAttempt();
        $this->assertNull($session['Q03.RESPONSE']);
        $this->assertEquals('default', $session['Q03.RESPONSE2']->getValue());
        $this->assertEquals(1, $session['Q03.numAttempts']->getValue());

        // I should not be able to skip by providing an empty state.
        try {
            $session->endAttempt(new State());
        } catch (AssessmentTestSessionException $e) {
            $this->assertEquals(AssessmentTestSessionException::ASSESSMENT_ITEM_SKIPPING_FORBIDDEN, $e->getCode());
            $this->assertEquals("The Item Session 'Q03.0' is not allowed to be skipped.", $e->getMessage());

            // The session should not have changed.
            $this->assertNull($session['Q03.RESPONSE']);
            $this->assertEquals('default', $session['Q03.RESPONSE2']->getValue());
            $this->assertEquals(1, $session['Q03.numAttempts']->getValue());
        }

        // I should be able to skip by providing a null value for all RESPONSES (empty string is equivalent to NULL, as per QTI spec).
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))]));

        // I should be able to skip by providing a non-null value for at least one RESPONSE.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString('correct'))]));
        $this->assertNull($session['Q03.RESPONSE']);
        $this->assertEquals('correct', $session['Q03.RESPONSE2']->getValue());
        $this->assertEquals(2, $session['Q03.numAttempts']->getValue());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q03')[0]->getState());

        $session->moveNext();

        // -- Q04.
        // This is an informational item (No interactions/response declarations)
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();
        $this->assertEquals(1, $session['Q04.numAttempts']->getValue());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q04')[0]->getState());

        // !!! ALLOWSKIPPING = TRUE !!!

        // -- Q05.
        $session->beginAttempt();
        $session->endAttempt(new State());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getAssessmentItemSessions('Q05')[0]->getState());

        $session->moveNext();

        // -- Q06.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getAssessmentItemSessions('Q06')[0]->getState());

        $session->moveNext();

        // -- Q07.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))]));
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getAssessmentItemSessions('Q07')[0]->getState());

        $session->moveNext();

        /// !!! ALLOWSKIPPING = FALSE, But, ignored because the current submission mode is simultaneous !!!

        // -- Q08.
        $session->beginAttempt();
        $session->endAttempt(new State());

        $session->moveNext();

        // -- Q09.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        $session->moveNext();

        // -- Q10.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER), new ResponseVariable('RESPONSE2', Cardinality::SINGLE, BaseType::STRING, new QtiString(''))]));

        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q08')[0]->getState());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q09')[0]->getState());
        $this->assertEquals(AssessmentItemSessionState::SUSPENDED, $session->getAssessmentItemSessions('Q10')[0]->getState());

        // Simultaneous submission mode test part ends...
        $session->moveNext();

        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getAssessmentItemSessions('Q08')[0]->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getAssessmentItemSessions('Q09')[0]->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $session->getAssessmentItemSessions('Q10')[0]->getState());
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }

    public function testPathTracking()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/nonlinear_5_items_unlimited_attempts.xml', false, AssessmentTestSession::PATH_TRACKING);

        $this->assertSame([], $assessmentTestSession->getPath());
        $this->assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

        $assessmentTestSession->beginTestSession();

        $this->assertSame([], $assessmentTestSession->getPath());
        $this->assertEquals(0, $assessmentTestSession->getRoute()->getPosition());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(
            new State(
                [
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')),
                ]
            )
        );

        $assessmentTestSession->moveNext();
        $this->assertSame([0], $assessmentTestSession->getPath());
        $this->assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveBack();
        $this->assertSame([], $assessmentTestSession->getPath());
        $this->assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Try to move back on the very first route item in the flow...
        $assessmentTestSession->moveBack();
        $this->assertSame([], $assessmentTestSession->getPath());
        $this->assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0], $assessmentTestSession->getPath());
        $this->assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump to the 4th route item in the flow.
        $assessmentTestSession->jumpTo(3);
        $this->assertSame([0, 1], $assessmentTestSession->getPath());
        $this->assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump to the 1st route item in the flow.
        $assessmentTestSession->jumpTo(0);
        $this->assertSame([], $assessmentTestSession->getPath());
        $this->assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump to the 1st route item in the flow again.
        $assessmentTestSession->jumpTo(0);
        $this->assertSame([], $assessmentTestSession->getPath());
        $this->assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0], $assessmentTestSession->getPath());
        $this->assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0, 1], $assessmentTestSession->getPath());
        $this->assertEquals(2, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0, 1, 2], $assessmentTestSession->getPath());
        $this->assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump at the same position.
        $assessmentTestSession->jumpTo(3);
        $this->assertSame([0, 1, 2], $assessmentTestSession->getPath());
        $this->assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0, 1, 2, 3], $assessmentTestSession->getPath());
        $this->assertEquals(4, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveBack();
        $this->assertSame([0, 1, 2], $assessmentTestSession->getPath());
        $this->assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0, 1, 2, 3], $assessmentTestSession->getPath());
        $this->assertEquals(4, $assessmentTestSession->getRoute()->getPosition());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this->assertSame([0, 1, 2, 3, 4], $assessmentTestSession->getPath());
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
    }

    public function testJumpNotAllowed()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/linear_5_items.xml');
        $assessmentTestSession->beginTestSession();

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Jumps are not allowed in LINEAR navigation mode.");
        $assessmentTestSession->jumpTo(1);
    }

    public function testAlwaysAllowJumps()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/linear_5_items.xml', false, AssessmentTestSession::ALWAYS_ALLOW_JUMPS);
        $assessmentTestSession->beginTestSession();

        $assessmentTestSession->jumpTo(1);
        $this->assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
    }

    public function testSetSessionIdEmptyString()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'sessionId' argument must be a non-empty string.");

        $assessmentTestSession->setSessionId('');
    }

    public function testSetSessionIdWrongType()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'sessionId' argument must be a string, 'integer' given.");

        $assessmentTestSession->setSessionId(999);
    }

    public function testSetStateWrongType()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The state argument must be a value from the AssessmentTestSessionState enumeration");

        $assessmentTestSession->setState(true);
    }

    public function testEndTestSessionAlreadyFinished()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();
        $assessmentTestSession->endTestSession();

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot end the test session while the state of the test session is INITIAL or CLOSED.");

        $assessmentTestSession->endTestSession();
    }

    public function testBeginAttemptNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot begin an attempt for the current item while the state of the test session is INITIAL or CLOSED.");

        $assessmentTestSession->beginAttempt();
    }

    public function testEndAttemptNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot end an attempt for the current item while the state of the test session is INITIAL or CLOSED.");

        $assessmentTestSession->endAttempt(new State());
    }

    public function testMoveNextNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot move to the next item while the test session state is INITIAL or CLOSED.");

        $assessmentTestSession->moveNext();
    }

    public function testMoveBackNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot move to the previous item while the test session state is INITIAL or CLOSED.");

        $assessmentTestSession->moveBack();
    }

    public function testJumpWhenLinearNavigationMode()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Jumps are not allowed in LINEAR navigation mode.");
        $this->expectExceptionCode(
            AssessmentTestSessionException::FORBIDDEN_JUMP
        );

        $assessmentTestSession->jumpTo(1);
    }

    public function testJumpWhenNonLinearNavigationModeOutOfBounds()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_nonlinear_singlesection.xml');
        $assessmentTestSession->beginTestSession();

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Position '1337' is out of the Route boundaries.");
        $this->expectExceptionCode(
            AssessmentTestSessionException::FORBIDDEN_JUMP
        );

        $assessmentTestSession->jumpTo(1337);
    }

    public function testgetCurrentAssessmentItemRefOccurenceNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $this->assertFalse($assessmentTestSession->getCurrentAssessmentItemRefOccurence());
    }

    public function testIsCurrentAssessmentItemAdaptiveNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot know if the current item is adaptive while the state of the test session is INITIAL or CLOSED.");

        $assessmentTestSession->isCurrentAssessmentItemAdaptive();
    }

    public function testIsCurrentAssessmentItemInteractingNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage("Cannot know if the current item is in INTERACTING state while the state of the test session INITIAL or CLOSED.");

        $assessmentTestSession->isCurrentAssessmentItemInteracting();
    }

    public function testWhichLastOccurenceUpdateWrongType()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The 'assessmentItemRef' argument must be a string or an AssessmentItemRef object.");

        $assessmentTestSession->whichLastOccurenceUpdate(999);
    }

    public function testCanMoveBackwardPositionZero()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_nonlinear_singlesection.xml');
        $assessmentTestSession->beginTestSession();
        $this->assertFalse($assessmentTestSession->canMoveBackward());
    }

    public function testCanMoveBackwardPositionNonZero()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_nonlinear_singlesection.xml');
        $assessmentTestSession->beginTestSession();
        $assessmentTestSession->moveNext();
        $this->assertTrue($assessmentTestSession->canMoveBackward());
    }

    public function testCanMoveBackwardPositionNonZeroLinear()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession->beginTestSession();
        $assessmentTestSession->moveNext();
        $this->assertFalse($assessmentTestSession->canMoveBackward());
    }

    public function testIsNextRouteItemPredictible()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/route_item_prediction.xml');

        // Cannot be predicted while not running.
        $this->assertFalse($assessmentTestSession->isNextRouteItemPredictible());

        // Q01 - Can predict that next item is Q02.
        $assessmentTestSession->beginTestSession();
        $this->assertTrue($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q02 - Cannot predict that next item Q03 because it contains preConditions.
        $this->assertFalse($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q03 - Can predict that next item is Q04.
        $this->assertTrue($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q04 - Cannot predict that next item is Q05 because Q04 has branchRules.
        $this->assertFalse($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q05 - Cannot predict next item because Q05 is the very last item.
        $this->assertFalse($assessmentTestSession->isNextRouteItemPredictible());

        $assessmentTestSession->moveNext();
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
    }

    public function testGetWeightWrongType()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The given identifier argument is not a string, nor a VariableIdentifier object.");

        $assessmentTestSession->getWeight(999);
    }

    public function testSetVariableWithPrefixInIdentifier()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The variables set to the AssessmentTestSession global scope must have simple variable identifiers. 'TEST.Q01' given.");

        $assessmentTestSession->setVariable(new OutcomeVariable('TEST.Q01', Cardinality::SINGLE, BaseType::IDENTIFIER));
    }

    public function testSetVariableWithInvalidIdentifier()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The identifier '999' of the variable to set is invalid.");

        $assessmentTestSession->setVariable(new OutcomeVariable('999', Cardinality::SINGLE, BaseType::IDENTIFIER));
    }

    public function testOffsetGetWithInvalidVariableIdentifier()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("AssessmentTestSession object addressed with an invalid identifier '999'.");

        $assessmentTestSession[999];
    }

    public function testOffsetSetWrongType()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("An AssessmentTestSession object must be addressed by string.");

        $assessmentTestSession[999] = new QtiIdentifier('XXX');
    }

    public function testOffsetSetNonExistingVariable()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The variable 'abcd' to be set does not exist in the current context.");

        $assessmentTestSession['abcd'] = new QtiIdentifier('XXX');
    }

    public function testOffsetSetInvalidIdentifier()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("AssessmentTestSession object addressed with an invalid identifier '---999'.");

        $assessmentTestSession['---999'] = new QtiIdentifier('XXX');
    }

    public function testOffsetUnset()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $assessmentTestSession['SCORE'] = new QtiFloat(1.);
        $this->assertEquals(1.0, $assessmentTestSession['SCORE']->getValue());
        unset($assessmentTestSession['SCORE']);
        $this->assertNull($assessmentTestSession['SCORE']);
    }

    public function testOffsetUnsetInvalidIdentifier()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The variable identifier '---8888' is not a valid variable identifier.");

        unset($assessmentTestSession['---8888']);
    }

    public function testOffsetUnsetUnexistingVariable()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The variable 'SCOREX' does not exist in the AssessmentTestSession's global scope.");

        unset($assessmentTestSession['SCOREX']);
    }

    public function testOffsetUnsetNonGlobalScopeVariable()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfBoundsException::class);
        $this->expectExceptionMessage("The variable 'SCOREX' does not exist in the AssessmentTestSession's global scope.");

        unset($assessmentTestSession['SCOREX']);
    }

    public function testOffsetExistz()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $this->assertTrue(isset($assessmentTestSession['SCORE']));
    }

    public function testOffsetExistInvalidVariableIdentifier()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("The variable identifier '----989' is not a valid variable identifier.");

        unset($assessmentTestSession['----989']);
    }

    public function testOffsetExistsNonGlobalScope()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $this->expectException(OutOfRangeException::class);
        $this->expectExceptionMessage("Test existence of a variable in an AssessmentTestSession may only be addressed with simple variable identifiers (no prefix, no sequence number). 'QX.ITEMVAR' given.");

        isset($assessmentTestSession['QX.ITEMVAR']);
    }

    public function testGetCandidateStateNotRunning()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');
        $this->assertFalse($assessmentTestSession->getCandidateState());
    }

    public function testVisitNonAdaptiveTestPartButAdaptiveTestPartsExist()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/nonadaptive_adaptive.xml');

        // No item sessions at all should be selected at this time.
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q01'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q02'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q03'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));

        $assessmentTestSession->beginTestSession();

        // Only item session from testPart 'P01' should be selected at this time.
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q01'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q02'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q03'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));

        $assessmentTestSession->moveNext();
        $assessmentTestSession->moveNext();

        // Enter testPart 'P02'...
        $assessmentTestSession->moveNext();
        // At that moment, only the very first item session from testPart 'P02' because it is an adaptive test part.
        $this->assertEquals('Q04', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));

        $assessmentTestSession->moveNext();
        // Q05 is not selected because of the branch rules.
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertEquals('Q06', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));
    }

    public function testVisitAdaptiveTestPartButNonAdaptiveTestPartsExist()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/adaptive_nonadaptive.xml');

        // No item sessions at all should be selected at this time.
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q01'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q02'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q03'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));

        $assessmentTestSession->beginTestSession();

        // Only item session 'Q01' from testPart 'P01' should be selected at this time because it is adaptive.
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q01'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q02'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q03'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));

        // Because of the branchRule, Q02 will be skipped.
        $assessmentTestSession->moveNext();
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q01'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q02'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q03'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));

        // Now, by moving to Q04, the rest of item sessions will be selected.
        $assessmentTestSession->moveNext();
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q01'));
        $this->assertFalse($assessmentTestSession->getAssessmentItemSessions('Q02'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q03'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q05'));
        $this->assertNotFalse($assessmentTestSession->getAssessmentItemSessions('Q06'));
    }
}
