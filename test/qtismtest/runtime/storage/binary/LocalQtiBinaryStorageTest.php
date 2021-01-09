<?php

namespace qtismtest\runtime\storage\binary;

use DateTime;
use DateTimeZone;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiPair;
use qtism\common\datatypes\QtiPoint;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\QtiComponentIterator;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\common\MultipleContainer;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\storage\binary\LocalQtiBinaryStorage;
use qtism\runtime\storage\common\StorageException;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;
use qtism\common\datatypes\QtiFile;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiInteger;

/**
 * Class LocalQtiBinaryStorageTest
 */
class LocalQtiBinaryStorageTest extends QtiSmTestCase
{
    public function testLocalQtiBinaryStorage()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $test = $doc->getDocumentComponent();

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Instantiating the test session does not mean it is persisted. At the moment,
        // it is not persistent yet.
        $this::assertFalse($storage->exists($sessionId));

        $this::assertInstanceOf(AssessmentTestSession::class, $session);
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());

        // The candidate begins the test session at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();

        // A little bit of noisy persistence...
        $storage->persist($session);

        // Now the test is persisted, we can try to know whether it exists in storage.
        $this::assertTrue($storage->exists($sessionId));

        // Let's retrive the session from storage.
        $session = $storage->retrieve($sessionId);

        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());

        // The test session has begun. We are in linear mode so that all
        // item sessions must be initialized and selected for presentation
        // to the candidate.
        $itemSessionStore = $session->getAssessmentItemSessionStore();
        $itemSessionCount = 0;
        $iterator = new QtiComponentIterator($doc->getDocumentComponent(), ['assessmentItemRef']);
        foreach ($iterator as $itemRef) {
            $refItemSessions = $itemSessionStore->getAssessmentItemSessions($itemRef);
            foreach ($refItemSessions as $refItemSession) {
                $this::assertEquals(AssessmentItemSessionState::INITIAL, $refItemSession->getState());
                $itemSessionCount++;
            }
        }
        $this::assertEquals(9, $itemSessionCount);

        // The outcome variables composing the test-level global scope
        // must be set with their default value if any.
        foreach ($doc->getDocumentComponent()->getOutcomeDeclarations() as $outcomeDeclaration) {
            $this::assertFalse(is_null($session[$outcomeDeclaration->getIdentifier()]));
            $this::assertEquals(0, $session[$outcomeDeclaration->getIdentifier()]->getValue());
        }

        // S01 -> Q01 - Correct response.
        $this::assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
        $this::assertEquals(0.0, $session['Q01.scoring']->getValue());
        $this::assertSame(null, $session['Q01.RESPONSE']);

        // The candidate begins the attempt on Q01 at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The canditate spends 1 second on item Q01.
        $session->setTime(new DateTime('2014-07-14T13:00:01+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));

        // Are durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));

        $session->moveNext();

        // Because Q01 is not a multi-occurence item in the route, isLastOccurenceUpdate always return false.
        $this::assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // After the persist, do we still have the correct scores and durations for Q01?.
        $this::assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());
        $this::assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));

        // S01 -> Q02 - Incorrect response.
        $this::assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals('S01', $session->getCurrentAssessmentSection()->getIdentifier());
        $this::assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());

        // The candidate begins an attempt on Q02 at 13:00:02.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends 2 seconds on item Q02.
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('C', 'M')]))]));

        // Whate about scores of Q02?
        $this::assertInstanceOf(QtiFloat::class, $session['Q02.SCORE']);
        $this::assertEquals(1.0, $session['Q02.SCORE']->getValue());

        // Are the durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT4S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT4S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT4S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));

        $session->moveNext();

        // S01 -> Q03 - Skip.
        // The candidate begins an attempt on Q03 at 13:00:04.
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends 10 seconds on Q03 and then skip the item.
        $session->setTime(new DateTime('2014-07-14T13:00:14+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State());

        // Are the durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));

        // !!! We move to the next section S02.
        $session->moveNext();

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // S02 -> Q04 - Correct response.
        $this::assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals('S02', $session->getCurrentAssessmentSection()->getIdentifier());
        $this::assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());

        // The candidate begins an attempt on Q04 at 13:00:15.
        $session->setTime(new DateTime('2014-07-14T13:00:15+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends 5 seconds on Q04.
        $session->setTime(new DateTime('2014-07-14T13:00:20+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('W', 'G1'), new QtiDirectedPair('Su', 'G2')]))]));

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // What about score of Q04.
        $this::assertInstanceOf(QtiFloat::class, $session['Q04.SCORE']);
        $this::assertEquals(3.0, $session['Q04.SCORE']->getValue());

        // Check that after persist, the route position is still the same...
        $this::assertEquals(3, $session->getRoute()->getPosition());
        $storage->persist($session);
        $this::assertEquals(3, $session->getRoute()->getPosition());

        $session = $storage->retrieve($sessionId);
        $this::assertTrue($session['Q04.RESPONSE']->equals(new MultipleContainer(BaseType::DIRECTED_PAIR, [new QtiDirectedPair('W', 'G1'), new QtiDirectedPair('Su', 'G2')])));

        // Are the durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT20S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT20S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT6S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q04.duration']->equals(new QtiDuration('PT5S')));

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        $session->moveNext();

        // S02 -> Q05 - Skip.
        // The candidate begins the attempt on Q05 at 13:00:20.
        $session->setTime(new DateTime('2014-07-14T13:00:20+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends 1 second on Q05.
        $session->setTime(new DateTime('2014-07-14T13:00:21+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State());

        // Are the durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT21S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT21S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT7S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q04.duration']->equals(new QtiDuration('PT5S')));
        $this::assertTrue($session['Q05.duration']->equals(new QtiDuration('PT1S')));

        // !!! We move to the next section S03.
        $session->moveNext();

        // Q06 - Skip.
        // The candidate begins the attempt on Q06 at 13:00:24.
        $session->setTime(new DateTime('2014-07-14T13:00:24+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends 2 seconds on Q06.
        $session->setTime(new DateTime('2014-07-14T13:00:26+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State());

        // Are the durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT26S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT26S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT12S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT0S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q04.duration']->equals(new QtiDuration('PT5S')));
        $this::assertTrue($session['Q05.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q06.duration']->equals(new QtiDuration('PT2S')));

        // !!! We move to the next section S03.
        $session->moveNext();

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // S03 -> Q07.1 - Incorrect response (but inside the circle).
        $this::assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());

        // The candidate begins an attempt on Q07.1 at 13:00:28.
        $session->setTime(new DateTime('2014-07-14T13:00:28+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends 10 seconds on Q07.1.
        $session->setTime(new DateTime('2014-07-14T13:00:38+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(103, 114))]));

        // Are the durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT38S')));
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT38S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT12S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT12S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q04.duration']->equals(new QtiDuration('PT5S')));
        $this::assertTrue($session['Q05.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q06.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q07.1.duration']->equals(new QtiDuration('PT10S')));

        $session->moveNext();

        // We now test the lastOccurence update for this multi-occurence item.
        $this::assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this::assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));

        // S03 -> Q07.2 - Incorrect response (outside the circle).
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());

        // The candidate begins the attempt on Q07.2 at 13:00:38.
        $session->setTime(new DateTime('2014-07-14T13:00:38+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate spends a whole minute on Q07.2.
        $session->setTime(new DateTime('2014-07-14T13:01:38+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(200, 200))]));

        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT98S'))); // NO FEAR!
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT1M38S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT12S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT1M12S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q04.duration']->equals(new QtiDuration('PT5S')));
        $this::assertTrue($session['Q05.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q06.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q07.1.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q07.2.duration']->equals(new QtiDuration('PT1M')));

        $session->moveNext();

        $this::assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this::assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        $this::assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this::assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));

        // S03 -> Q07.3 - Correct response (perfectly on the point).
        $this::assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this::assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());

        // The candidate takes an attempt on Q07.3 at 13:01:39
        $session->setTime(new DateTime('2014-07-14T13:01:39+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();

        // The candidate takes an hour (yes, an hour) to respond on Q07.3.
        $session->setTime(new DateTime('2014-07-14T14:01:39+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]));
        $session->moveNext();

        // -- End of test, outcome processing performed correctly?

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this::assertInstanceOf(QtiInteger::class, $session['NCORRECTS01']);
        $this::assertEquals(1, $session['NCORRECTS01']->getValue());
        $this::assertInstanceOf(QtiInteger::class, $session['NCORRECTS02']);
        $this::assertEquals(1, $session['NCORRECTS02']->getValue());
        $this::assertInstanceOf(QtiInteger::class, $session['NCORRECTS03']);
        $this::assertEquals(1, $session['NCORRECTS03']->getValue());
        $this::assertEquals(6, $session['NINCORRECT']->getValue());
        $this::assertEquals(6, $session['NRESPONSED']->getValue());
        $this::assertEquals(9, $session['NPRESENTED']->getValue());
        $this::assertEquals(9, $session['NSELECTED']->getValue());
        $this::assertInstanceOf(QtiFloat::class, $session['PERCENT_CORRECT']);
        $this::assertEquals(round(33.33333, 3), round($session['PERCENT_CORRECT']->getValue(), 3));

        // -- End of test, are durations correct?
        $this::assertTrue($session['itemsubset.duration']->equals(new QtiDuration('PT3699S'))); // NO FEAR!
        $this::assertTrue($session['P01.duration']->equals(new QtiDuration('PT1H1M39S')));
        $this::assertTrue($session['S01.duration']->equals(new QtiDuration('PT14S')));
        $this::assertTrue($session['S02.duration']->equals(new QtiDuration('PT12S')));
        $this::assertTrue($session['S03.duration']->equals(new QtiDuration('PT1H1M13S')));
        $this::assertTrue($session['Q01.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q02.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q03.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q04.duration']->equals(new QtiDuration('PT5S')));
        $this::assertTrue($session['Q05.duration']->equals(new QtiDuration('PT1S')));
        $this::assertTrue($session['Q06.duration']->equals(new QtiDuration('PT2S')));
        $this::assertTrue($session['Q07.1.duration']->equals(new QtiDuration('PT10S')));
        $this::assertTrue($session['Q07.2.duration']->equals(new QtiDuration('PT1M')));
        $this::assertTrue($session['Q07.3.duration']->equals(new QtiDuration('PT1H')));
    }

    public function testLinearNavigationSimultaneousSubmission()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        $test = $doc->getDocumentComponent();

        $factory = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($factory, $test);
        $sessionId = 'linearSimultaneous1337';
        $session = $storage->instantiate(0, $sessionId);
        $session->beginTestSession();

        // Nothing in pending responses. The test has just begun.
        $this::assertEquals(0, count($session->getPendingResponseStore()->getAllPendingResponses()));

        // Q01 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(1, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this::assertEquals(null, $session['Q01.RESPONSE']);
        $this::assertEquals(0.0, $session['Q01.scoring']->getValue());

        // Q02 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M'), new QtiPair('D', 'L')]))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertSame(null, $session['Q02.RESPONSE']);
        $this::assertEquals(0.0, $session['Q02.SCORE']->getValue());
        $this::assertEquals(2, count($session->getPendingResponseStore()->getAllPendingResponses()));

        // Q03 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(3, count($session->getPendingResponseStore()->getAllPendingResponses()));

        // Q04 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(4, count($session->getPendingResponseStore()->getAllPendingResponses()));

        // Q05 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(5, count($session->getPendingResponseStore()->getAllPendingResponses()));

        // Q06 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(6, count($session->getPendingResponseStore()->getAllPendingResponses()));

        // Q07.1 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(7, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this::assertSame(null, $session['Q07.1.RESPONSE']);
        $this::assertEquals(0.0, $session['Q07.1.SCORE']->getValue());

        // Q07.2 - Incorrect but in the circle
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(103, 114))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertEquals(8, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this::assertSame(null, $session['Q07.2.RESPONSE']);
        $this::assertEquals(0.0, $session['Q07.2.SCORE']->getValue());

        // Q07.3 - Incorrect and out of the circle
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(30, 13))]));
        $this::assertSame(null, $session['Q07.3.RESPONSE']);
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // Response processing should have taken place beauce this is the end of the current test part.
        // The Pending Response Store should be then flushed and now empty.
        $this::assertEquals(0, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this::assertEquals(0.0, $session['Q07.3.SCORE']->getValue());
        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // Let's check the overall Assessment Test Session state.
        $this::assertInstanceOf(QtiIdentifier::class, $session['Q01.RESPONSE']);
        $this::assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this::assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
        $this::assertEquals(1.0, $session['Q01.scoring']->getValue());

        $this::assertTrue($session['Q02.RESPONSE']->equals(new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M'), new QtiPair('D', 'L')])));
        $this::assertEquals(4.0, $session['Q02.SCORE']->getValue());

        $this::assertInstanceOf(QtiFloat::class, $session['Q03.SCORE']);
        $this::assertEquals(0.0, $session['Q03.SCORE']->getValue());

        $this::assertInstanceOf(QtiFloat::class, $session['Q04.SCORE']);
        $this::assertEquals(0.0, $session['Q04.SCORE']->getValue());

        $this::assertInstanceOf(QtiFloat::class, $session['Q05.SCORE']);
        $this::assertEquals(0.0, $session['Q05.SCORE']->getValue());

        $this::assertInstanceOf(QtiFloat::class, $session['Q06.mySc0r3']);
        $this::assertEquals(0.0, $session['Q06.mySc0r3']->getValue());

        $this::assertTrue($session['Q07.1.RESPONSE']->equals(new QtiPoint(102, 113)));
        $this::assertEquals(1.0, $session['Q07.1.SCORE']->getValue());

        $this::assertTrue($session['Q07.2.RESPONSE']->equals(new QtiPoint(103, 114)));
        $this::assertEquals(1.0, $session['Q07.2.SCORE']->getValue());

        $this::assertTrue($session['Q07.3.RESPONSE']->equals(new QtiPoint(30, 13)));
        $this::assertInstanceOf(QtiFloat::class, $session['Q07.3.SCORE']);
        $this::assertEquals(0.0, $session['Q07.3.SCORE']->getValue());

        $this::assertEquals(2, $session['NCORRECTS01']->getValue());
        $this::assertEquals(0, $session['NCORRECTS02']->getValue());
        $this::assertEquals(1, $session['NCORRECTS03']->getValue());
        $this::assertEquals(6, $session['NINCORRECT']->getValue());
        $this::assertEquals(5, $session['NRESPONSED']->getValue());
        $this::assertEquals(9, $session['NPRESENTED']->getValue());
        $this::assertEquals(9, $session['NSELECTED']->getValue());
        $this::assertEquals(round(33.33333, 3), round($session['PERCENT_CORRECT']->getValue(), 3));
    }

    public function testNonLinear()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps.xml');
        $test = $doc->getDocumentComponent();

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $session->beginTestSession();
        $sessionId = $session->getSessionId();

        // It's instantiated, but not persisted.

        // Fun test #1, delete the non-persisted test session.
        $this::assertFalse($storage->delete($session));

        $storage->persist($session);

        // Fun test#2, delete the persisted test session.
        $this::assertTrue($storage->delete($session));

        // Fun test#3, retrieve an unexisting test session.
        try {
            $session = $storage->retrieve($sessionId);
        } catch (StorageException $e) {
            $this::assertTrue(true, 'An Exception should be thrown because the test session does not exist anymore.');
        }
    }

    public function testFiles()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/files/files.xml');
        $test = $doc->getDocumentComponent();

        $fileManager = new FileSystemFileManager();
        $sessionManager = new SessionManager($fileManager);
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $session->beginTestSession();
        $sessionId = $session->getSessionId();

        // --- Q01 - files_1.txt = ('text.txt', 'text/plain', 'Some text...')
        $session->beginAttempt();
        $filepath = self::samplesDir() . 'datatypes/file/raw/files_1.txt';
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, $fileManager->createFromFile($filepath, 'text/plain', 'text.txt'))]));
        $session->moveNext();
        $this::assertInstanceOf(QtiFile::class, $session['Q01.RESPONSE']);
        $this::assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this::assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this::assertEquals("Some text...\n", $session['Q01.RESPONSE']->getData());

        // Let's persist and retrieve and look if we have the same value in Q01.RESPONSE.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);
        $this::assertInstanceOf(QtiFile::class, $session['Q01.RESPONSE']);
        $this::assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this::assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this::assertEquals("Some text...\n", $session['Q01.RESPONSE']->getData());

        // --- Q02 - files_2.txt = ('', 'text/html', '<img src="/qtism/img.png"/>')
        $session->beginAttempt();
        $filepath = self::samplesDir() . 'datatypes/file/raw/files_2.txt';
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, $fileManager->createFromFile($filepath, 'text/html'))]));
        $session->moveNext();
        $this::assertInstanceOf(QtiFile::class, $session['Q02.RESPONSE']);
        $this::assertEquals('', $session['Q02.RESPONSE']->getFilename());
        $this::assertEquals('text/html', $session['Q02.RESPONSE']->getMimeType());
        $this::assertEquals("<img src=\"/qtism/img.png\"/>\n", $session['Q02.RESPONSE']->getData());

        // Again, we persist and retrieve.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // We now test all the collected variables.
        $this::assertInstanceOf(QtiFile::class, $session['Q01.RESPONSE']);
        $this::assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this::assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this::assertEquals("Some text...\n", $session['Q01.RESPONSE']->getData());

        $this::assertInstanceOf(QtiFile::class, $session['Q02.RESPONSE']);
        $this::assertEquals('', $session['Q02.RESPONSE']->getFilename());
        $this::assertEquals('text/html', $session['Q02.RESPONSE']->getMimeType());
        $this::assertEquals("<img src=\"/qtism/img.png\"/>\n", $session['Q02.RESPONSE']->getData());

        // --- Q03 - files_3.txt ('empty.txt', 'text/plain', '')
        $session->beginAttempt();
        $filepath = self::samplesDir() . 'datatypes/file/raw/files_3.txt';
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, $fileManager->createFromFile($filepath, 'text/plain', 'empty.txt'))]));
        $session->moveNext();
        $this::assertFalse($session->isRunning());
        $this::assertInstanceOf(QtiFile::class, $session['Q02.RESPONSE']);
        $this::assertEquals('empty.txt', $session['Q03.RESPONSE']->getFilename());
        $this::assertEquals('text/plain', $session['Q03.RESPONSE']->getMimeType());
        $this::assertEquals('', $session['Q03.RESPONSE']->getData());

        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // Final big check.
        $this::assertInstanceOf(QtiFile::class, $session['Q01.RESPONSE']);
        $this::assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this::assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this::assertEquals("Some text...\n", $session['Q01.RESPONSE']->getData());

        $this::assertInstanceOf(QtiFile::class, $session['Q02.RESPONSE']);
        $this::assertEquals('', $session['Q02.RESPONSE']->getFilename());
        $this::assertEquals('text/html', $session['Q02.RESPONSE']->getMimeType());
        $this::assertEquals("<img src=\"/qtism/img.png\"/>\n", $session['Q02.RESPONSE']->getData());

        $this::assertInstanceOf(QtiFile::class, $session['Q02.RESPONSE']);
        $this::assertEquals('empty.txt', $session['Q03.RESPONSE']->getFilename());
        $this::assertEquals('text/plain', $session['Q03.RESPONSE']->getMimeType());
        $this::assertEquals('', $session['Q03.RESPONSE']->getData());

        // 2nd final big check with deletion of the session.
        // Related files should also be deleted.

        // -- Check files exists where they should be on the file system.
        $this::assertTrue(file_exists($path1 = $session['Q01.RESPONSE']->getPath()));
        $this::assertTrue(file_exists($path2 = $session['Q02.RESPONSE']->getPath()));
        $this::assertTrue(file_exists($path3 = $session['Q03.RESPONSE']->getPath()));

        $storage->delete($session);

        // -- Check files that files are removed from the file system after deletion of the session.
        $this::assertFalse(file_exists($path1));
        $this::assertFalse(file_exists($path2));
        $this::assertFalse(file_exists($path3));
    }

    public function testTemplateProcessingBasic1()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/templates/template_processing_test_simple.xml');
        $test = $doc->getDocumentComponent();

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Let's try to persist a not begun session.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // The session is instantiated, but not yet begun.
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());

        // The session begins...
        $session->beginTestSession();
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());

        // We are in linear, non adaptive test. In this context, all item sessions
        // should be already begun.
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];

        // The GOODSCORE and WRONGSCORE variable values should not have changed yet. Indeed,
        // we are in a linear test part. In such a context, template processing occurs
        // just prior the first attempt.
        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this::assertNull($QTPL1Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertEquals(0.0, $session['QTPL1.GOODSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL1Session['GOODSCORE']->getValue());
        $this::assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());

        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this::assertNull($QTPL2Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertEquals(0.0, $session['QTPL2.GOODSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL2Session['GOODSCORE']->getValue());
        $this::assertEquals(0.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL2Session['WRONGSCORE']->getValue());

        // Now let's make sure the persistence works correctly when templating is in force...
        // We do this by testing again that default values and values are stil the same after
        // persistence.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this::assertNull($QTPL1Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertEquals(0.0, $session['QTPL1.GOODSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL1Session['GOODSCORE']->getValue());
        $this::assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());

        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this::assertNull($QTPL2Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertEquals(0.0, $session['QTPL2.GOODSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL2Session['GOODSCORE']->getValue());
        $this::assertEquals(0.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL2Session['WRONGSCORE']->getValue());

        // It seems to be ok! Let's take the test! By beginning the first attempt,
        // template processing is applied on QTPL1. However, anything related to QTPL2 should not
        // have changed.
        $session->beginAttempt();

        // Let's check the values. Q01 should be affected by template processing, QTPL2 should not.
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INTERACTING, $QTPL1Session->getState());
        $this::assertEquals(1.0, $QTPL1Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this::assertEquals(0.0, $QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this::assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this::assertEquals(1.0, $QTPL1Session['GOODSCORE']->getValue());
        $this::assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this::assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());

        // TPL1's responses should be applied their default values if any at the
        // beginning of the first attempt.
        $this::assertEquals('ChoiceB', $session['QTPL1.RESPONSE']->getValue());

        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // TPL1's response should still be at their default.
        $this::assertEquals('ChoiceB', $session['QTPL1.RESPONSE']->getValue());

        // -- TPL1 - Correct response.
        $candidateResponses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $session->endAttempt($candidateResponses);

        $this::assertEquals(1.0, $session['QTPL1.SCORE']->getValue());

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals('ChoiceA', $session['QTPL1.RESPONSE']->getValue());
        $this::assertEquals(1.0, $session['QTPL1.SCORE']->getValue());

        $session->moveNext();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // -- TPL2 - Correct response.
        $session->beginAttempt();

        // QTPL2 should now be affected by Templat eProcessing.
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INTERACTING, $QTPL2Session->getState());
        $this::assertEquals(2.0, $QTPL2Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this::assertEquals(-1.0, $QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this::assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this::assertEquals(2.0, $QTPL2Session['GOODSCORE']->getValue());
        $this::assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this::assertEquals(-1.0, $QTPL2Session['WRONGSCORE']->getValue());

        // TPL2's responses should be at their default values if any at
        // the beginning of the first attempt.
        $this::assertEquals('ChoiceA', $session['QTPL2.RESPONSE']->getValue());

        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // TPL2's response should still be at their default.
        $this::assertEquals('ChoiceA', $session['QTPL2.RESPONSE']->getValue());

        // -- TPL2 - Incorrect response.
        $candidateResponses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]);
        $session->endAttempt($candidateResponses);

        $this::assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals('ChoiceC', $session['QTPL2.RESPONSE']->getValue());
        $this::assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());

        // -- Go to the end of test.
        $session->moveNext();

        // Check states...
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $QTPL1Session->getState());
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $QTPL2Session->getState());
    }

    public function testTemplateDefault1()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/templates/template_default_test_simple_linear.xml');
        $test = $doc->getDocumentComponent();

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Let's try to persist a not begun session.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // The session is instantiated, but not yet begun.
        $this::assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());

        // The session begins...
        $session->beginTestSession();
        $this::assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());

        // We are in linear, non adaptive test. In this context, all item sessions
        // should be already begun.
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];

        // The the session is correctly instantiated, with the <templateDefault>s in force.
        // In linear mode, the templateDefaults are applied just before the first attempt.
        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this::assertNull($QTPL1Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertNull($session['QTPL1.GOODSCORE']);
        $this::assertNull($QTPL1Session['GOODSCORE']);
        $this::assertNull($session['QTPL1.WRONGSCORE']);
        $this::assertNull($QTPL1Session['WRONGSCORE']);

        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this::assertNull($QTPL2Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertNull($session['QTPL2.GOODSCORE']);
        $this::assertNull($QTPL2Session['GOODSCORE']);
        $this::assertNull($session['QTPL2.WRONGSCORE']);
        $this::assertNull($QTPL2Session['WRONGSCORE']);

        // Now let's make sure the persistence works correctly when <templateDefault>s are in force...
        // We do this by testing again that default values are correctly initialized within their respective
        // item sessions...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this::assertNull($QTPL1Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertNull($session['QTPL1.GOODSCORE']);
        $this::assertNull($QTPL1Session['GOODSCORE']);
        $this::assertNull($session['QTPL1.WRONGSCORE']);
        $this::assertNull($QTPL1Session['WRONGSCORE']);

        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this::assertNull($QTPL2Session->getVariable('GOODSCORE')->getDefaultValue());
        $this::assertNull($QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue());
        $this::assertNull($session['QTPL2.GOODSCORE']);
        $this::assertNull($QTPL2Session['GOODSCORE']);
        $this::assertNull($session['QTPL2.WRONGSCORE']);
        $this::assertNull($QTPL2Session['WRONGSCORE']);

        // It seems to be ok! Let's take the test!
        $session->beginAttempt();
        // TPL1's responses should be applied their default values if any at the
        // beginning of the first attempt.
        $this::assertEquals(null, $session['QTPL1.RESPONSE']);
        $this::assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());

        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // TPL1's response should still be at their default.
        $this::assertEquals(null, $session['QTPL1.RESPONSE']);

        // -- TPL1 - Correct response.
        $candidateResponses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]);
        $session->endAttempt($candidateResponses);

        $this::assertEquals(1.0, $session['QTPL1.SCORE']->getValue());

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals('ChoiceA', $session['QTPL1.RESPONSE']->getValue());
        $this::assertEquals(1.0, $session['QTPL1.SCORE']->getValue());

        $session->moveNext();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // -- TPL2 - Correct response.
        $session->beginAttempt();

        // TPL2's responses should be at their default values if any at
        // the beginning of the first attempt.
        $this::assertEquals(null, $session['QTPL2.RESPONSE']);

        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // TPL2's response should still be at their default.
        $this::assertEquals(null, $session['QTPL2.RESPONSE']);

        // -- TPL2 - Incorrect response.
        $candidateResponses = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceC'))]);
        $session->endAttempt($candidateResponses);

        $this::assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals('ChoiceC', $session['QTPL2.RESPONSE']->getValue());
        $this::assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());

        // -- Go to the end of test.
        $session->moveNext();

        // Check states...
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $QTPL1Session->getState());
        $this::assertEquals(AssessmentItemSessionState::CLOSED, $QTPL2Session->getState());
    }

    public function testVisitedTestPartsLinear1TestPart()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/testparts/linear_1_testparts.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        $this::assertFalse($session->isTestPartVisited('P01'));

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $session->beginTestSession();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $session->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this::assertTrue($session->isTestPartVisited('P01'));
    }

    public function testVisitedTestPartsLinear2TestPart()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/testparts/linear_2_testparts.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertFalse($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));

        $session->beginTestSession();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));

        $session->moveNext();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));

        $session->moveNext();

        $this::assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));
    }

    public function testVisitedTestPartsNonLinear3TestPartJumpBeginningOfTestPart()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/testparts/nonlinear_3_testparts.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertFalse($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $session->beginTestSession();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter P03 on Q07, which is the first item in P03.
        $session->jumpTo(6);

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertTrue($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter 03 on Q06, which is the last item in P02.
        $session->moveBack();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));
        $this::assertTrue($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));
    }

    public function testVisitedTestPartsNonLinear3TestPartJumpMiddleOfTestPart()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/testparts/nonlinear_3_testparts.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertFalse($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));

        $session->beginTestSession();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter P03 on Q08, which is the item in the middle of P03.
        $session->jumpTo(7);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertTrue($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // Enter 03 on Q05, which is the item in the middle of P02.
        $session->jumpTo(4);
        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));
        $this::assertTrue($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));
    }

    public function testVisitedTestPartsNonLinear3TestPartJumpEndOfTestPart()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/testparts/nonlinear_3_testparts.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertFalse($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));

        $session->beginTestSession();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        $session->moveNext();

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertFalse($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // Enter P03 on Q09, which is the item in the middle of P03.
        $session->jumpTo(8);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertFalse($session->isTestPartVisited('P02'));
        $this::assertTrue($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));

        // Enter 03 on Q04, which is the first item of P02.
        $session->jumpTo(3);

        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertTrue($session->isTestPartVisited('P01'));
        $this::assertTrue($session->isTestPartVisited('P02'));
        $this::assertTrue($session->isTestPartVisited('P03'));
        $this::assertTrue($session->isTestPartVisited($session->getCurrentTestPart()));
    }

    public function testConfigPersistence()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/linear_5_items.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $config = AssessmentTestSession::FORCE_BRANCHING | AssessmentTestSession::FORCE_PRECONDITIONS;
        $session = $storage->instantiate($config);
        $sessionId = $session->getSessionId();

        $this::assertEquals($config, $session->getConfig());

        // Check that after persist/retrieve, the configuration is still the same.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        $this::assertEquals($config, $session->getConfig());
    }
}
