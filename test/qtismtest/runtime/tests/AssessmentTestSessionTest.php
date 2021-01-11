<?php

namespace qtismtest\runtime\tests;

use InvalidArgumentException;
use OutOfBoundsException;
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
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\VariableIdentifier;
use qtism\runtime\tests\AssessmentItemSessionException;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestPlace;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionException;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;
use qtism\data\state\Weight;

/**
 * Class AssessmentTestSessionTest
 */
class AssessmentTestSessionTest extends QtiSmTestCase
{
    protected $state;

    public function setUp(): void
    {
        parent::setUp();

        $xml = new XmlCompactDocument();
        $xml->load(self::samplesDir() . 'custom/runtime/assessmenttest_context.xml');

        $sessionManager = new SessionManager();
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
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

        // You cannot get information on the current elements of
        // the test session when INITIAL state is in force.
        $this::assertFalse($assessmentTestSession->getCurrentAssessmentItemRef());
        $this::assertFalse($assessmentTestSession->getCurrentAssessmentSection());
        $this::assertFalse($assessmentTestSession->getCurrentNavigationMode());
        $this::assertFalse($assessmentTestSession->getCurrentSubmissionMode());
        $this::assertFalse($assessmentTestSession->getCurrentTestPart());
        $this::assertFalse($assessmentTestSession->getCurrentRemainingAttempts());

        $assessmentTestSession->beginTestSession();
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Now that the test session has begun, you can get information
        // about the current elements of the session.
        $this::assertEquals('P01', $assessmentTestSession->getCurrentTestPart()->getIdentifier());
        $this::assertEquals('S01', $assessmentTestSession->getCurrentAssessmentSection()->getIdentifier());
        $this::assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertIsInt($assessmentTestSession->getCurrentNavigationMode());
        $this::assertEquals(NavigationMode::LINEAR, $assessmentTestSession->getCurrentNavigationMode());
        $this::assertIsInt($assessmentTestSession->getCurrentSubmissionMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $assessmentTestSession->getCurrentSubmissionMode());
        $this::assertEquals(1, $assessmentTestSession->getCurrentRemainingAttempts());

        // test-level outcome variables should be initialized
        // with their default values.
        $this::assertInstanceOf(QtiFloat::class, $assessmentTestSession['SCORE']);
        $this::assertEquals(0.0, $assessmentTestSession['SCORE']->getValue());

        // No session ID should be set, this is the role of AssessmentTestSession Storage Services.
        $this::assertEquals('no_session_id', $assessmentTestSession->getSessionId());
    }

    public function testInstantiateTwo()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();
        // check Q01.1, Q01.2, Q01.3 item sessions are not all initialized.
        for ($i = 1; $i <= 3; $i++) {
            if ($i === 1) {
                $score = $assessmentTestSession["Q01.${i}.SCORE"];
                $response = $assessmentTestSession["Q01.${i}.RESPONSE"];
                $this::assertInstanceOf(QtiFloat::class, $score);
                $this::assertEquals(0.0, $score->getValue());
                $this::assertNull($response);
            } else {
                $score = $assessmentTestSession["Q01.${i}.SCORE"];
                $response = $assessmentTestSession["Q01.${i}.RESPONSE"];
                $this::assertNull($score);
                $this::assertNull($response);
            }
        }
    }

    public function testSetVariableValuesAfterInstantiationOne()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        // Change the value of the global SCORE.
        $this::assertEquals(0.0, $assessmentTestSession['SCORE']->getValue());
        $assessmentTestSession['SCORE'] = new QtiFloat(20.0);
        $this::assertEquals(20.0, $assessmentTestSession['SCORE']->getValue());

        // the assessment test session has no variable MAXSCORE.
        $this::assertNull($assessmentTestSession['MAXSCORE']);
        try {
            $assessmentTestSession['MAXSCORE'] = new QtiFloat(20.0);
            // An exception must be thrown in this case!
            $this::assertTrue(false);
        } catch (OutOfBoundsException $e) {
            $this::assertTrue(true);
        }

        // Change the value of Q01.SCORE.
        $this::assertEquals(0.0, $assessmentTestSession['Q01.SCORE']->getValue());
        $assessmentTestSession['Q01.SCORE'] = new QtiFloat(1.0);
        $this::assertEquals(1.0, $assessmentTestSession['Q01.SCORE']->getValue());

        // Q01 has no 'MAXSCORE' variable.
        $this::assertNull($assessmentTestSession['Q01.MAXSCORE']);
        try {
            $assessmentTestSession['Q01.MAXSCORE'] = new QtiFloat(1.0);
            // An exception must be thrown !
            $this::assertTrue(false);
        } catch (OutOfBoundsException $e) {
            $this::assertTrue(true);
        }

        // No item Q04.
        $this::assertNull($assessmentTestSession['Q04.SCORE']);
        try {
            $assessmentTestSession['Q04.SCORE'] = new QtiFloat(1.0);
            // Because no such item, outofbounds.
            $this::assertTrue(false);
        } catch (OutOfBoundsException $e) {
            $this::assertTrue(true);
        }
    }

    public function testLinearSkipAll()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        $this::assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
        $this::assertEquals('S01', $assessmentTestSession->getCurrentAssessmentSection()->getIdentifier());
        $this::assertEquals('P01', $assessmentTestSession->getCurrentTestPart()->getIdentifier());
        $this::assertFalse($assessmentTestSession->isCurrentAssessmentItemAdaptive());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->skip();
        $assessmentTestSession->moveNext();
        $this::assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
        $this::assertFalse($assessmentTestSession->isCurrentAssessmentItemAdaptive());

        $this::assertEquals(1, $assessmentTestSession->getCurrentRemainingAttempts());
        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->skip();
        $assessmentTestSession->moveNext();
        $this::assertEquals('Q03', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $assessmentTestSession->getCurrentAssessmentItemRefOccurence());
        $this::assertFalse($assessmentTestSession->isCurrentAssessmentItemAdaptive());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->skip();
        $assessmentTestSession->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
        $this::assertFalse($assessmentTestSession->getCurrentAssessmentItemRef());
        $this::assertFalse($assessmentTestSession->getCurrentAssessmentSection());
        $this::assertFalse($assessmentTestSession->getCurrentTestPart());
        $this::assertFalse($assessmentTestSession->getCurrentNavigationMode());
        $this::assertFalse($assessmentTestSession->getCurrentSubmissionMode());
    }

    public function testLinearAnswerAll()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        // Q01 - Correct Response = 'ChoiceA'.
        $this::assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertFalse($assessmentTestSession->isCurrentAssessmentItemInteracting());
        $assessmentTestSession->beginAttempt();
        $this::assertTrue($assessmentTestSession->isCurrentAssessmentItemInteracting());
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')));
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();
        $this::assertFalse($assessmentTestSession->isCurrentAssessmentItemInteracting());

        // Q02 - Correct Response = 'ChoiceB'.
        $this::assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $assessmentTestSession->beginAttempt();
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))); // -> incorrect x)
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();

        // Q03 - Correct Response = 'ChoiceC'.
        $this::assertEquals('Q03', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $assessmentTestSession->beginAttempt();
        $responses = new State();
        $responses->setVariable(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC')));
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();

        // Check the final state of the test session.
        // - Q01
        $this::assertEquals('ChoiceA', $assessmentTestSession['Q01.RESPONSE']->getValue());
        $this::assertInstanceOf(QtiFloat::class, $assessmentTestSession['Q01.SCORE']);
        $this::assertEquals(1.0, $assessmentTestSession['Q01.SCORE']->getValue());
        $this::assertInstanceOf(QtiInteger::class, $assessmentTestSession['Q01.numAttempts']);
        $this::assertEquals(1, $assessmentTestSession['Q01.numAttempts']->getValue());

        // - Q02
        $this::assertEquals('ChoiceC', $assessmentTestSession['Q02.RESPONSE']->getValue());
        $this::assertInstanceOf(QtiFloat::class, $assessmentTestSession['Q02.SCORE']);
        $this::assertEquals(0.0, $assessmentTestSession['Q02.SCORE']->getValue());
        $this::assertInstanceOf(QtiInteger::class, $assessmentTestSession['Q02.numAttempts']);
        $this::assertEquals(1, $assessmentTestSession['Q02.numAttempts']->getValue());

        // - Q03
        $this::assertEquals('ChoiceC', $assessmentTestSession['Q03.RESPONSE']->getValue());
        $this::assertInstanceOf(QtiFloat::class, $assessmentTestSession['Q03.SCORE']);
        $this::assertEquals(1.0, $assessmentTestSession['Q03.SCORE']->getValue());
        $this::assertInstanceOf(QtiInteger::class, $assessmentTestSession['Q03.numAttempts']);
        $this::assertEquals(1, $assessmentTestSession['Q03.numAttempts']->getValue());

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
    }

    public function testLinearSimultaneousSubmission()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        $this::assertTrue($doc->getDocumentComponent()->isExclusivelyLinear());
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        // Q01 - Correct.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        // !!! The Response must be stored in the session, but no score must be computed.
        // This is the same for the next items.
        $this::assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this::assertEquals(0.0, $session['Q01.scoring']->getValue());
        $this::assertCount(1, $session->getPendingResponses());

        // Q02 - Incorrect (but SCORE = 3)
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M')]))]));
        $session->moveNext();
        $this::assertTrue($session['Q02.RESPONSE']->equals(new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M')])));
        $this::assertEquals(0.0, $session['Q02.SCORE']->getValue());
        $this::assertCount(2, $session->getPendingResponses());

        // Q03 - Skip.
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();
        // When skipping, the pending responses consist of all response variable
        // with their default value applied.
        $this::assertCount(3, $session->getPendingResponses());

        // Q04 - Skip.
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();
        $this::assertCount(4, $session->getPendingResponses());

        // Q05 - Skip.
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();
        $this::assertCount(5, $session->getPendingResponses());

        // Q06 - Skip.
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();
        $this::assertCount(6, $session->getPendingResponses());

        // Q07.1 - Correct.
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]));
        $session->moveNext();
        $this::assertTrue($session['Q07.1.RESPONSE']->equals(new QtiPoint(102, 113)));
        $this::assertInstanceOf(QtiFloat::class, $session['Q07.1.SCORE']);
        $this::assertEquals(0.0, $session['Q07.1.SCORE']->getValue());
        $this::assertCount(7, $session->getPendingResponses());

        // Q07.2 - Incorrect (but SCORE = 1).
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(103, 113))]));
        $session->moveNext();
        $this::assertTrue($session['Q07.2.RESPONSE']->equals(new QtiPoint(103, 113)));
        $this::assertEquals(0.0, $session['Q07.2.SCORE']->getValue());
        $this::assertCount(8, $session->getPendingResponses());

        // Q07.3 - Incorrect (and SCORE = 0).
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(50, 60))]));
        $session->moveNext();
        $this::assertTrue($session['Q07.3.RESPONSE']->equals(new QtiPoint(50, 60)));
        $this::assertEquals(0.0, $session['Q07.3.SCORE']->getValue());

        // This is the end of the test. Then, the pending responses were flushed.
        // We also have to check if the deffered response processing took place.
        $this::assertCount(0, $session->getPendingResponses());

        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());
        $this::assertEquals(3.0, $session['Q02.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q03.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q04.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q05.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q06.mySc0r3']->getValue());

        // Did the test-level outcome processing take place?
        $this::assertEquals(9, $session['NPRESENTED']->getValue());
    }

    /**
     * @dataProvider linearOutcomeProcessingProvider
     *
     * @param array $responses
     * @param array $outcomes
     * @throws AssessmentTestSessionException
     * @throws PhpStorageException
     * @throws XmlStorageException
     * @throws AssessmentItemSessionException
     */
    public function testLinearOutcomeProcessing(array $responses, array $outcomes)
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        // There must be 8 outcome variables to be checked:
        // NCORRECTS01, NCORRECTS02, NCORRECTS03, NINCORRECT, NRESPONDED
        // NPRESENTED, NSELECTED, PERCENT_CORRECT.
        $this::assertEquals(['NCORRECTS01', 'NCORRECTS02', 'NCORRECTS03', 'NINCORRECT', 'NRESPONSED', 'NPRESENTED', 'NSELECTED', 'PERCENT_CORRECT'], array_keys($outcomes));

        // The selection of items for the test is 9.
        $this::assertCount(9, $responses);

        foreach ($responses as $resp) {
            $assessmentTestSession->beginAttempt();
            $assessmentTestSession->endAttempt($resp);
            $assessmentTestSession->moveNext();
        }

        $this::assertFalse($assessmentTestSession->isRunning());
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());

        foreach ($outcomes as $outcomeIdentifier => $outcomeValue) {
            $this::assertInstanceOf(($outcomeValue instanceof QtiInteger) ? QtiInteger::class : QtiFloat::class, $assessmentTestSession[$outcomeIdentifier]);

            if ($outcomeIdentifier !== 'PERCENT_CORRECT') {
                $this::assertEquals($outcomeValue->getValue(), $assessmentTestSession[$outcomeIdentifier]->getValue());
            } else {
                $this::assertEquals(round($outcomeValue->getValue(), 2), round($assessmentTestSession[$outcomeIdentifier]->getValue(), 2));
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
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        $this::assertFalse($assessmentTestSession->whichLastOccurenceUpdate($doc->getDocumentComponent()->getComponentByIdentifier('Q01')));

        $responses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();

        $this::assertEquals(0, $assessmentTestSession->whichLastOccurenceUpdate('Q01'));

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->skip();
        $assessmentTestSession->moveNext();
        $this::assertEquals(0, $assessmentTestSession->whichLastOccurenceUpdate('Q01'));

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt($responses);
        $assessmentTestSession->moveNext();
        $this::assertEquals(2, $assessmentTestSession->whichLastOccurenceUpdate('Q01'));
    }

    public function testGetAssessmentItemSessions()
    {
        // --- Test with single occurence items.
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        foreach (['Q01', 'Q02', 'Q03'] as $identifier) {
            $sessions = $assessmentTestSession->getAssessmentItemSessions($identifier);
            $this::assertCount(1, $sessions);
            $this::assertEquals($identifier, $sessions[0]->getAssessmentItem()->getIdentifier());
        }

        // Malformed $identifier.
        try {
            $sessions = $assessmentTestSession->getAssessmentItemSessions('Q04.1');
            $this::assertFalse(true);
        } catch (InvalidArgumentException $e) {
            $this::assertTrue(true);
        }

        // Unknown assessmentItemRef.
        $this::assertFalse($assessmentTestSession->getAssessmentItemSessions('Q04'));

        // --- Test with multiple occurence items.
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection_withreplacement.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        $sessions = $assessmentTestSession->getAssessmentItemSessions('Q01');
        $this::assertCount(3, $sessions);
        for ($i = 0; $i < count($sessions); $i++) {
            $this::assertEquals('Q01', $sessions[$i]->getAssessmentItem()->getIdentifier());
        }
    }

    public function testGetPreviousRouteItem()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        // Try to get the previous route item but... there is no one because
        // we are at the first item.
        try {
            $previousRouteItem = $session->getPreviousRouteItem();

            // An Exception should have been thrown.
            $this::assertrue(false, 'An exception should have been thrown.');
        } catch (OutOfBoundsException $e) {
            // Exception successfuly thrown.
            $this::assertTrue(true);
        }

        // Q01.
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q02.
        $previousRouteItem = $session->getPreviousRouteItem();
        $this::assertEquals('Q01', $previousRouteItem->getAssessmentItemRef()->getIdentifier());
    }

    public function testNextRouteItem()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        // Q01
        $nextRouteItem = $session->getNextRouteItem();
        $this::assertEquals('Q02', $nextRouteItem->getAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q02
        $nextRouteItem = $session->getNextRouteItem();
        $this::assertEquals('Q03', $nextRouteItem->getAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q03
        // There is no more next route items.
        try {
            $nextRouteItem = $session->getNextRouteItem();
            $this::assertTrue(false, 'An exception should have been thrown.');
        } catch (OutOfBoundsException $e) {
            // Exception successfuly thrown dude!
            $this::assertTrue(true);
        }
    }

    public function testPossibleJumpsTestPart()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        // The session has not begun, the candidate is not able to jump anywhere.
        $this::assertCount(0, $session->getPossibleJumps(false));

        $session->beginTestSession();
        $jumps = $session->getPossibleJumps(AssessmentTestPlace::TEST_PART);
        $this::assertCount(6, $jumps);
        $this::assertEquals('Q01', $jumps[0]->getTarget()->getAssessmentItemRef()->getIdentifier('Q01'));
        $this::assertEquals(0, $jumps[0]->getPosition());
        $this::assertEquals(AssessmentItemSessionState::INITIAL, $jumps[0]->getItemSession()->getState());
        $this::assertEquals('Q02', $jumps[1]->getTarget()->getAssessmentItemRef()->getIdentifier('Q02'));
        $this::assertEquals(1, $jumps[1]->getPosition());
        $this::assertEquals('Q03', $jumps[2]->getTarget()->getAssessmentItemRef()->getIdentifier('Q03'));
        $this::assertEquals(2, $jumps[2]->getPosition());
        $this::assertEquals('Q04', $jumps[3]->getTarget()->getAssessmentItemRef()->getIdentifier('Q04'));
        $this::assertEquals(3, $jumps[3]->getPosition());
        $this::assertEquals('Q05', $jumps[4]->getTarget()->getAssessmentItemRef()->getIdentifier('Q05'));
        $this::assertEquals(4, $jumps[4]->getPosition());
        $this::assertEquals('Q06', $jumps[5]->getTarget()->getAssessmentItemRef()->getIdentifier('Q06'));
        $this::assertEquals(5, $jumps[5]->getPosition());

        // The session has begun, the candidate is able to jump anywhere in testPart 'P01'.
        for ($i = 0; $i < 6; $i++) {
            $session->beginAttempt();
            $session->skip();
            $session->moveNext();
        }

        // We should be now in testPart 'PO2'.
        $this::assertEquals('P02', $session->getCurrentTestPart()->getIdentifier());
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        $jumps = $session->getPossibleJumps(AssessmentTestPlace::TEST_PART);
        $this::assertCount(3, $jumps);
        $this::assertEquals('Q07', $jumps[0]->getTarget()->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(6, $jumps[0]->getPosition());
        $this::assertEquals(AssessmentItemSessionState::INITIAL, $jumps[0]->getItemSession()->getState());
        $this::assertEquals(0, $jumps[0]->getTarget()->getOccurence());
        $this::assertEquals('Q07', $jumps[1]->getTarget()->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(7, $jumps[1]->getPosition());
        $this::assertEquals(1, $jumps[1]->getTarget()->getOccurence());
        $this::assertEquals('Q07', $jumps[2]->getTarget()->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(8, $jumps[2]->getPosition());
        $this::assertEquals(2, $jumps[2]->getTarget()->getOccurence());

        for ($i = 0; $i < 3; $i++) {
            $session->beginAttempt();
            $session->skip();
            $session->moveNext();
        }

        // This is the end of the test session so no more possible jumps.
        $this::assertCount(0, $session->getPossibleJumps(false));
    }

    public function testPossibleJumpsWholeTest()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/routeitem_position.xml');
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        $jumps = $session->getPossibleJumps();
        $this::assertCount(12, $jumps);
    }

    public function testJumps()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $session->beginTestSession();

        // Begin attempt at Q01.
        $session->beginAttempt();

        // Moving to Q03 and answer it.
        $session->jumpTo(2);

        // Check that session for Q01 is suspended after jump.
        $Q01s = $session->getAssessmentItemSessions('Q01');
        $this::assertEquals(AssessmentItemSessionState::SUSPENDED, $Q01s[0]->getState());
        $this::assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());

        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('H'), new QtiIdentifier('O')]))]));
        $session->moveNext();
        $this::assertEquals(2.0, $session['Q03.SCORE']->getValue());

        // Come back at Q01.
        $session->jumpTo(0);
        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();
        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());

        // Autoforward enabled so we are at Q02.
        $this::assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P')]))]));
        $session->moveNext();
        $this::assertEquals(2.0, $session['Q02.SCORE']->getValue());

        // Q03 Again because of autoforward.
        $this::assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        try {
            $session->beginAttempt();
            // Only a single attemp allowed.
            $this::assertFalse(true, 'Only a single attempt is allowed for Q03.');
        } catch (AssessmentTestSessionException $e) {
            // The assessment item session is closed.
            $this::assertEquals(AssessmentTestSessionException::STATE_VIOLATION, $e->getCode());
        }

        // Move to Q07.2
        $session->jumpTo(7);
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 102))]));
        $session->moveNext();
        $this::assertEquals(1.0, $session['Q07.2.SCORE']->getValue());

        // Q07.3
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // End of test, everything ok?
        $this::assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
        $this::assertInstanceOf(QtiFloat::class, $session['Q02.SCORE']);
        $this::assertInstanceOf(QtiFloat::class, $session['Q03.SCORE']);
        $this::assertInstanceOf(QtiFloat::class, $session['Q04.SCORE']); // Because auto forward = true, Q04 was selected as eligible after Q03's endAttempt. However, it was never attempted.
        $this::assertSame(0.0, $session['Q05.SCORE']->getValue());
        $this::assertSame(0.0, $session['Q06.mySc0r3']->getValue());
        $this::assertSame(0.0, $session['Q07.1.SCORE']->getValue());
        $this::assertInstanceOf(QtiFloat::class, $session['Q07.2.SCORE']);
        $this::assertInstanceOf(QtiFloat::class, $session['Q07.3.SCORE']);

        $this::assertEquals(5, $session['NPRESENTED']->getValue());
        $this::assertEquals(9, $session['NSELECTED']->getValue());
    }

    public function testJumpsSimultaneous()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps_simultaneous.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $session->beginTestSession();

        // Begin attempt at Q01.
        $session->beginAttempt();

        // Moving to Q03 and answer it.
        $session->jumpTo(2);
        $this::assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::IDENTIFIER, new MultipleContainer(BaseType::IDENTIFIER, [new QtiIdentifier('H'), new QtiIdentifier('O')]))]));
        $session->moveNext();

        // Come back at Q01.
        $session->jumpTo(0);
        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        // Autoforward enabled so we are at Q02.
        $this::assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P')]))]));
        $session->moveNext();

        // Q03 Again because of autoforward.
        $this::assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        try {
            $session->beginAttempt();
            // Only a single attemp allowed.
            $this::assertFalse(true, 'Only a single attempt is allowed for Q03.');
        } catch (AssessmentTestSessionException $e) {
            // The assessment test session is closed.
            $this::assertEquals(AssessmentTestSessionException::STATE_VIOLATION, $e->getCode());
        }

        $this::assertEquals('Q03', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        // Go back in testPart P01 to complete it. Q04, Q05 and Q06 must be responsed.
        $session->jumpTo(3);
        // Q04
        $this::assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q05
        $this::assertEquals('Q05', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q06
        $this::assertEquals('Q06', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q07.1
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        // Jump to Q07.3
        $session->jumpTo(8);
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());

        // Jump to Q07.1
        $session->jumpTo(6);
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q07.2
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q07.3 already answered.
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Outcome processing has now taken place. Everything OK?
        $this::assertEquals(2.0, $session['Q03.SCORE']->getValue());
        $this::assertEquals(2.0, $session['Q02.SCORE']->getValue());
        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());
        $this::assertEquals(0.0, $session['Q04.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q05.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q06.mySc0r3']->getValue());
        $this::assertEquals(0.0, $session['Q07.1.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q07.2.SCORE']->getValue());
        $this::assertEquals(0.0, $session['Q07.3.SCORE']->getValue());

        $this::assertEquals(9, $session['NSELECTED']->getValue());
        $this::assertEquals(9, $session['NPRESENTED']->getValue());
    }

    public function testJumpNotAllowed()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/linear_5_items.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        $this->expectException(AssessmentTestSessionException::class);
        $this->expectExceptionMessage('Jumps are not allowed in LINEAR navigation mode.');
        $assessmentTestSession->jumpTo(1);
    }

    public function testAlwaysAllowJumps()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/linear_5_items.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();
        $assessmentTestSession->setAlwaysAllowJumps(true);

        $assessmentTestSession->jumpTo(1);
        $this::assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
    }

    public function testMoveNextAndBackNonLinearIndividual()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_nonlinear.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $session->beginTestSession();
        $this::assertEquals(NavigationMode::NONLINEAR, $session->getCurrentNavigationMode());
        $this::assertEquals(SubmissionMode::INDIVIDUAL, $session->getCurrentSubmissionMode());

        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->moveNext();
        $this::assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->moveBack();
        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());

        try {
            // We are at the very first route item and want to move back... ouch!
            $session->moveBack();
        } catch (AssessmentTestSessionException $e) {
            $this::assertEquals(AssessmentTestSessionException::LOGIC_ERROR, $e->getCode());
        }

        // We should still be on Q01.
        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext(); // Q02
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q03
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q04
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q05
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q06
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q07.1
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q07.2
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();// Q07.3

        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // OutcomeProcessing?
        $this::assertInstanceOf(QtiFloat::class, $session['PERCENT_CORRECT']);
        $this::assertEquals(0.0, $session['PERCENT_CORRECT']->getValue());
        $this::assertEquals(9, $session['NSELECTED']->getValue());
    }

    public function testMoveNextAndBackNonLinearSimultaneous()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps_simultaneous.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $session->beginTestSession();
        $this::assertEquals(NavigationMode::NONLINEAR, $session->getCurrentNavigationMode());
        $this::assertEquals(SubmissionMode::SIMULTANEOUS, $session->getCurrentSubmissionMode());

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
        $session->skip();
        $session->moveNext();

        // Q05
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // Q06.
        // (no scores computed yet).
        $this::assertEquals(0.0, $session['Q01.scoring']->getValue());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // We are now in another test part and some scores were processed for test part P01.
        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());
    }

    public function testUnlimitedAttempts()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/unlimited_attempts.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        $this::assertEquals(-1, $session->getCurrentRemainingAttempts());
        $session->beginAttempt();
        $this::assertEquals(-1, $session->getCurrentRemainingAttempts());
        $session->skip();
        $this::assertEquals(-1, $session->getCurrentRemainingAttempts());

        $session->beginAttempt();
        $this::assertEquals(-1, $session->getCurrentRemainingAttempts());
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceB'))]));
        $this::assertEquals(-1, $session->getCurrentRemainingAttempts());

        $session->moveNext();
        $this::assertEquals(-1, $session->getCurrentRemainingAttempts());
    }

    public function testSuspendInteractItemSession()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/unlimited_attempts.xml');

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $session->beginTestSession();

        // Finally, suspend an item session in interacting state by moving to the next item during an attempt.
        $this::assertEquals(AssessmentItemSessionState::INITIAL, $session->getCurrentAssessmentItemSession()->getState());
        $session->beginAttempt();
        $this::assertEquals(AssessmentItemSessionState::INTERACTING, $session->getCurrentAssessmentItemSession()->getState());
        $previousItemSession = $session->getCurrentAssessmentItemSession();
        $session->moveNext();
        $this::assertEquals(AssessmentItemSessionState::SUSPENDED, $previousItemSession->getState());

        // Try to re-enter interacting state.
        $previousItemSession = $session->getCurrentAssessmentItemSession();
        $session->moveBack(); // We did not interact, then it remains INITIAL...
        $this::assertEquals(AssessmentItemSessionState::INITIAL, $previousItemSession->getState());
        $this::assertEquals(AssessmentItemSessionState::INTERACTING, $session->getCurrentAssessmentItemSession()->getState());

        // Finally answer the question :) !
        $responses = new State([new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceA'))]);
        $session->endAttempt($responses);
        $session->moveNext();
        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());

        // Q02...
        $session->beginAttempt();
        $this::assertEquals(AssessmentItemSessionState::INTERACTING, $session->getCurrentAssessmentItemSession()->getState());
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
        $this::assertInstanceOf(Weight::class, $weight);
        $this::assertEquals($v->getVariableName(), $weight->getIdentifier());
        $this::assertEquals($expectedValue, $weight->getValue());
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
        $this::assertIsBool($weight);
        $this::assertFalse($weight);
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
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/routeitem_assessmentsections.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());

        $route = $assessmentTestSession->getRoute();

        // Route[0] - S01 -> S01A -> Q01
        $this::assertEquals('Q01', $route->getRouteItemAt(0)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(0)->getAssessmentSections();
        $this::assertCount(2, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertTrue(isset($assessmentSections['S01A']));
        // The returned assessment section must be the nearest parent section.
        $this::assertEquals('S01A', $route->getRouteItemAt(0)->getAssessmentSection()->getIdentifier());

        // Route[1] - S01 -> S01A -> Q02
        $this::assertEquals('Q02', $route->getRouteItemAt(1)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(1)->getAssessmentSections();
        $this::assertCount(2, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertTrue(isset($assessmentSections['S01A']));

        // Check for the order (from to to bottom of the hierarchy)
        $this::assertEquals(['S01', 'S01A'], $assessmentSections->getKeys());
        $this::assertEquals('S01A', $route->getRouteItemAt(1)->getAssessmentSection()->getIdentifier());

        // Route[2] - S01 -> S01A -> Q03
        $this::assertEquals('Q03', $route->getRouteItemAt(2)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(2)->getAssessmentSections();
        $this::assertCount(2, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertTrue(isset($assessmentSections['S01A']));
        $this::assertEquals('S01A', $route->getRouteItemAt(0)->getAssessmentSection()->getIdentifier());

        // Route[3] - S01 -> S01B -> Q04
        $this::assertEquals('Q04', $route->getRouteItemAt(3)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(3)->getAssessmentSections();
        $this::assertCount(2, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertTrue(isset($assessmentSections['S01B']));
        $this::assertEquals('S01B', $route->getRouteItemAt(3)->getAssessmentSection()->getIdentifier());

        // Route[4] - S01 -> S01B -> Q05
        $this::assertEquals('Q05', $route->getRouteItemAt(4)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(4)->getAssessmentSections();
        $this::assertCount(2, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertTrue(isset($assessmentSections['S01B']));
        $this::assertEquals('S01B', $route->getRouteItemAt(4)->getAssessmentSection()->getIdentifier());

        // Route[5] - S01 -> S01B -> Q06
        $this::assertEquals('Q06', $route->getRouteItemAt(5)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(5)->getAssessmentSections();
        $this::assertCount(2, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S01']));
        $this::assertTrue(isset($assessmentSections['S01B']));
        $this::assertEquals('S01B', $route->getRouteItemAt(5)->getAssessmentSection()->getIdentifier());

        // Route[6] - S02 -> Q07
        $this::assertEquals('Q07', $route->getRouteItemAt(6)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(6)->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S02']));
        $this::assertEquals('S02', $route->getRouteItemAt(6)->getAssessmentSection()->getIdentifier());

        // Route[7] - S02 -> Q08
        $this::assertEquals('Q08', $route->getRouteItemAt(7)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(7)->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S02']));
        $this::assertEquals('S02', $route->getRouteItemAt(7)->getAssessmentSection()->getIdentifier());

        // Route[8] - S02 -> Q09
        $this::assertEquals('Q09', $route->getRouteItemAt(8)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(8)->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S02']));
        $this::assertEquals('S02', $route->getRouteItemAt(8)->getAssessmentSection()->getIdentifier());

        // Route[9] - S03 -> Q10
        $this::assertEquals('Q10', $route->getRouteItemAt(9)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(9)->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S03']));
        $this::assertEquals('S03', $route->getRouteItemAt(9)->getAssessmentSection()->getIdentifier());

        // Route[10] - S03 -> Q11
        $this::assertEquals('Q11', $route->getRouteItemAt(10)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(10)->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S03']));
        $this::assertEquals('S03', $route->getRouteItemAt(10)->getAssessmentSection()->getIdentifier());

        // Route[11] - S03 -> Q12
        $this::assertEquals('Q12', $route->getRouteItemAt(11)->getAssessmentItemRef()->getIdentifier());
        $assessmentSections = $route->getRouteItemAt(11)->getAssessmentSections();
        $this::assertCount(1, $assessmentSections);
        $this::assertTrue(isset($assessmentSections['S03']));
        $this::assertEquals('S03', $route->getRouteItemAt(11)->getAssessmentSection()->getIdentifier());

        // Make sure that the assessmentSections are provided in the right order.
        // For instance, the correct order for route[0] is [S01, S01A].
        $order = ['S01', 'S01A'];
        $sections = $route->getRouteItemAt(0)->getAssessmentSections();
        $this::assertEquals(count($order), count($sections));
        $i = 0;

        $sections->rewind();
        while ($sections->valid()) {
            $current = $sections->current();
            $this::assertEquals($order[$i], $current->getIdentifier());
            $i++;
            $sections->next();
        }
    }

    public function testGetItemSessionControl()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/routeitem_itemsessioncontrols.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());

        $route = $assessmentTestSession->getRoute();

        // Q01 - Must be under control of its own itemSessionControl.
        $control = $route->getRouteItemAt(0)->getItemSessionControl();
        $this::assertEquals(2, $control->getItemSessionControl()->getMaxAttempts());
        $this::assertSame($doc->getDocumentComponent()->getComponentByIdentifier('Q01'), $control->getOwner());

        // Q07 - Must be under control of the ItemSessionControl of the parent AssessmentSection.
        $control = $route->getRouteItemAt(6)->getItemSessionControl();
        $this::assertEquals(3, $control->getItemSessionControl()->getMaxAttempts());
        $this::assertSame($doc->getDocumentComponent()->getComponentByIdentifier('S02'), $control->getOwner());

        // Q10 - Is under no control.
        $control = $route->getRouteItemAt(9)->getItemSessionControl();
        $this::assertNull($control);

        // Q13 - Must be under control of the ItemSessionControl of the parent TestPart.
        $control = $route->getRouteItemAt(12)->getItemSessionControl();
        $this::assertEquals(4, $control->getItemSessionControl()->getMaxAttempts());
        $this::assertSame($doc->getDocumentComponent()->getComponentByIdentifier('P02'), $control->getOwner());
    }

    public function testGetTimeLimits()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/routeitem_timelimits.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());

        $route = $assessmentTestSession->getRoute();

        // Q01
        $timeLimits = $route->getRouteItemAt(0)->getTimeLimits();
        $this::assertCount(3, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(50, $timeLimits[2]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q02
        $timeLimits = $route->getRouteItemAt(1)->getTimeLimits();
        $this::assertCount(2, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q08
        $timeLimits = $route->getRouteItemAt(7)->getTimeLimits();
        $this::assertCount(3, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(150, $timeLimits[2]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q12
        $timeLimits = $route->getRouteItemAt(11)->getTimeLimits();
        $this::assertCount(2, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q13
        $timeLimits = $route->getRouteItemAt(12)->getTimeLimits();
        $this::assertCount(2, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(200, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Q14
        $timeLimits = $route->getRouteItemAt(13)->getTimeLimits();
        $this::assertCount(1, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));

        // Test item's timelimits exclusion.
        // Q01
        $timeLimits = $route->getRouteItemAt(0)->getTimeLimits(true);
        $this::assertCount(2, $timeLimits);
        $this::assertEquals(600, $timeLimits[0]->getTimeLimits()->getMaxTime()->getSeconds(true));
        $this::assertEquals(400, $timeLimits[1]->getTimeLimits()->getMaxTime()->getSeconds(true));
    }

    public function testRubricBlockRefsHierarchy()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/rubricblockrefs_hierarchy.xml', true);

        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $route = $session->getRoute();

        // S01 - S01A - Q01
        $rubricBlockRefs = $route->getRouteItemAt(0)->getRubricBlockRefs();
        $this::assertEquals(['RB00_MAIN', 'RB01_MATH', 'RB02_MATH'], $rubricBlockRefs->getKeys());

        // S01 - S01A - Q02
        $rubricBlockRefs = $route->getRouteItemAt(1)->getRubricBlockRefs();
        $this::assertEquals(['RB00_MAIN', 'RB01_MATH', 'RB02_MATH'], $rubricBlockRefs->getKeys());

        // S01 - S01B - Q03
        $rubricBlockRefs = $route->getRouteItemAt(2)->getRubricBlockRefs();
        $this::assertEquals(['RB00_MAIN', 'RB03_BIOLOGY'], $rubricBlockRefs->getKeys());

        // S01C - Q04
        $rubricBlockRefs = $route->getRouteItemAt(3)->getRubricBlockRefs();
        $this::assertCount(0, $rubricBlockRefs);
    }

    public function testRouteItemPosition()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/routeitem_position.xml');
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $route = $session->getRoute();

        // Q01 - position 0.
        $routeItem = $route->getRouteItemAt(0);
        $this::assertEquals('Q01', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $route->getRouteItemPosition($routeItem));

        // Q02 - position 1.
        $routeItem = $route->getRouteItemAt(1);
        $this::assertEquals('Q02', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(1, $route->getRouteItemPosition($routeItem));

        // ...

        // Q12 - position 11.
        $routeItem = $route->getRouteItemAt(11);
        $this::assertEquals('Q12', $routeItem->getAssessmentItemRef()->getIdentifier());
        $this::assertEquals(11, $route->getRouteItemPosition($routeItem));
    }

    public function testEmptySection()
    {
        // Aims at testing that even a section of the test is empty,
        // it is simply ignored at runtime.
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/empty_section.xml');
        $manager = new SessionManager();

        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->beginTestSession();

        // First section contains a single item.
        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->skip();
        $session->moveNext();

        // The second section is empty, moveNext() goes to the end of the current route,
        // and the session is then closed.
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }

    public function testPreserveOutcomes()
    {
        // Aims at testing that even a section of the test is empty,
        // it is simply ignored at runtime.
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/preserve_test_outcomes.xml');
        $manager = new SessionManager();

        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());
        $session->setPreservedOutcomeVariables(['PRESERVED']);
        $session->beginTestSession();

        $this::assertEquals('I will be preserved!', $session['PRESERVED']->getValue());
        $session['PRESERVED'] = new QtiString('I am still preserved!');

        $this::assertEquals(0, $session['NOTPRESERVED']->getValue());

        $this::assertEquals('Q01', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        $this::assertEquals('I am still preserved!', $session['PRESERVED']->getValue());
        $this::assertEquals(1, $session['NOTPRESERVED']->getValue());

        $this::assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceB'))]));
        $session->moveNext();

        $this::assertEquals('I am still preserved!', $session['PRESERVED']->getValue());
        $this::assertEquals(1, $session['NOTPRESERVED']->getValue());
    }

    public function testSuspendResume()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/scenario_basic_nonadaptive_linear_singlesection.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->beginTestSession();

        // Q01.
        $this::assertEquals('Q01', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
        $assessmentTestSession->beginAttempt();

        $assessmentTestSession->suspend();
        $this::assertEquals(AssessmentTestSessionState::SUSPENDED, $assessmentTestSession->getState());

        $assessmentTestSession->resume();
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->endAttempt(new State([]));
        $assessmentTestSession->moveNext();

        // Q02.
        $this::assertEquals('Q02', $assessmentTestSession->getCurrentAssessmentItemRef()->getIdentifier());
    }

    public function testGetRouteCountAllWithResponseDeclaration()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/route_count/all_with_responsedeclaration.xml');
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $this::assertEquals(3, $session->getRouteCount());
        $this::assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_ALL));
        $this::assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_EXCLUDENORESPONSE));
        $this::assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_FLOW));
    }

    public function testGetRouteCountMissingResponseDeclaration()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/route_count/missing_responsedeclaration.xml');
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $this::assertEquals(3, $session->getRouteCount());
        $this::assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_ALL));
        $this::assertEquals(2, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_EXCLUDENORESPONSE));
        $this::assertEquals(3, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_FLOW));
    }

    public function testGetRouteCountMixed()
    {
        $qti = new XmlDocument();
        $qti->load(self::samplesDir() . 'custom/tests/linear_nonLinear_multiple_testparts/test.xml');
        $doc = XmlCompactDocument::createFromXmlAssessmentTestDocument($qti);
        $manager = new SessionManager();
        $session = $manager->createAssessmentTestSession($doc->getDocumentComponent());

        $this::assertEquals(6, $session->getRouteCount());
        $this::assertEquals(6, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_ALL));
        $this::assertEquals(4, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_EXCLUDENORESPONSE));
        $this::assertEquals(5, $session->getRouteCount(AssessmentTestSession::ROUTECOUNT_FLOW));
    }

    public function testPathTracking()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/nonlinear_5_items_unlimited_attempts.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->setPathTracking(true);

        $this::assertSame([], $assessmentTestSession->getPath());
        $this::assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

        $assessmentTestSession->beginTestSession();

        $this::assertSame([], $assessmentTestSession->getPath());
        $this::assertEquals(0, $assessmentTestSession->getRoute()->getPosition());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(
            new State(
                [
                    new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA')),
                ]
            )
        );

        $assessmentTestSession->moveNext();
        $this::assertSame([0], $assessmentTestSession->getPath());
        $this::assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveBack();
        $this::assertSame([], $assessmentTestSession->getPath());
        $this::assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Try to move back on the very first route item in the flow...
        $assessmentTestSession->moveBack();
        $this::assertSame([], $assessmentTestSession->getPath());
        $this::assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0], $assessmentTestSession->getPath());
        $this::assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump to the 4th route item in the flow.
        $assessmentTestSession->jumpTo(3);
        $this::assertSame([0, 1], $assessmentTestSession->getPath());
        $this::assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump to the 1st route item in the flow.
        $assessmentTestSession->jumpTo(0);
        $this::assertSame([], $assessmentTestSession->getPath());
        $this::assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump to the 1st route item in the flow again.
        $assessmentTestSession->jumpTo(0);
        $this::assertSame([], $assessmentTestSession->getPath());
        $this::assertEquals(0, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0], $assessmentTestSession->getPath());
        $this::assertEquals(1, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0, 1], $assessmentTestSession->getPath());
        $this::assertEquals(2, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0, 1, 2], $assessmentTestSession->getPath());
        $this::assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        // Jump at the same position.
        $assessmentTestSession->jumpTo(3);
        $this::assertSame([0, 1, 2], $assessmentTestSession->getPath());
        $this::assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0, 1, 2, 3], $assessmentTestSession->getPath());
        $this::assertEquals(4, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveBack();
        $this::assertSame([0, 1, 2], $assessmentTestSession->getPath());
        $this::assertEquals(3, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0, 1, 2, 3], $assessmentTestSession->getPath());
        $this::assertEquals(4, $assessmentTestSession->getRoute()->getPosition());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $assessmentTestSession->getState());

        $assessmentTestSession->moveNext();
        $this::assertSame([0, 1, 2, 3, 4], $assessmentTestSession->getPath());
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
    }

    public function testIsNextRouteItemPredictible()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/route_item_prediction.xml');
        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());

        // Cannot be predicted while not running.
        $this::assertFalse($assessmentTestSession->isNextRouteItemPredictible());

        // Q01 - Can predict that next item is Q02.
        $assessmentTestSession->beginTestSession();
        $this::assertTrue($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q02 - Cannot predict that next item Q03 because it contains preConditions.
        $this::assertFalse($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q03 - Can predict that next item is Q04.
        $this::assertTrue($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q04 - Cannot predict that next item is Q05 because Q04 has branchRules.
        $this::assertFalse($assessmentTestSession->isNextRouteItemPredictible());
        $assessmentTestSession->moveNext();

        // Q05 - Cannot predict next item because Q05 is the very last item.
        $this::assertFalse($assessmentTestSession->isNextRouteItemPredictible());

        $assessmentTestSession->moveNext();
        $this::assertEquals(AssessmentTestSessionState::CLOSED, $assessmentTestSession->getState());
    }

    public function testOutcomeProcessingEnabled()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/outcome_processing/enable_disable_outcome_processing.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

        $assessmentTestSession->beginTestSession();
        $this::assertEquals('Hello world!', $assessmentTestSession['MYOUTCOME']->getValue());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(
            new State([
                new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceA')),
            ])
        );

        $this::assertEquals('This is me!', $assessmentTestSession['MYOUTCOME']->getValue());
    }

    public function testOutcomeProcessingDisabled()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/outcome_processing/enable_disable_outcome_processing.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $assessmentTestSession->setOutcomeProcessingEnabled(false);
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $assessmentTestSession->getState());

        $assessmentTestSession->beginTestSession();
        $this::assertEquals('Hello world!', $assessmentTestSession['MYOUTCOME']->getValue());

        $assessmentTestSession->beginAttempt();
        $assessmentTestSession->endAttempt(
            new State([
                new ResponseVariable('RESPONSE', BaseType::IDENTIFIER, Cardinality::SINGLE, new QtiIdentifier('ChoiceA')),
            ])
        );

        $this::assertEquals('Hello world!', $assessmentTestSession['MYOUTCOME']->getValue());
    }
}
