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
use qtism\common\datatypes\QtiString;
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
    public function testLocalQtiBinaryStorage(): void
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
            $this::assertNotNull($session[$outcomeDeclaration->getIdentifier()]);
            $this::assertEquals(0, $session[$outcomeDeclaration->getIdentifier()]->getValue());
        }

        // S01 -> Q01 - Correct response.
        $this::assertInstanceOf(QtiFloat::class, $session['Q01.scoring']);
        $this::assertEquals(0.0, $session['Q01.scoring']->getValue());
        $this::assertNull($session['Q01.RESPONSE']);

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

    public function testLinearNavigationSimultaneousSubmission(): void
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
        $this::assertCount(0, $session->getPendingResponseStore()->getAllPendingResponses());

        // Q01 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new QtiIdentifier('ChoiceA'))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(1, $session->getPendingResponseStore()->getAllPendingResponses());
        $this::assertNull($session['Q01.RESPONSE']);
        $this::assertEquals(0.0, $session['Q01.scoring']->getValue());

        // Q02 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, [new QtiPair('A', 'P'), new QtiPair('C', 'M'), new QtiPair('D', 'L')]))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertNull($session['Q02.RESPONSE']);
        $this::assertEquals(0.0, $session['Q02.SCORE']->getValue());
        $this::assertCount(2, $session->getPendingResponseStore()->getAllPendingResponses());

        // Q03 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(3, $session->getPendingResponseStore()->getAllPendingResponses());

        // Q04 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(4, $session->getPendingResponseStore()->getAllPendingResponses());

        // Q05 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(5, $session->getPendingResponseStore()->getAllPendingResponses());

        // Q06 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(6, $session->getPendingResponseStore()->getAllPendingResponses());

        // Q07.1 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(102, 113))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(7, $session->getPendingResponseStore()->getAllPendingResponses());
        $this::assertNull($session['Q07.1.RESPONSE']);
        $this::assertEquals(0.0, $session['Q07.1.SCORE']->getValue());

        // Q07.2 - Incorrect but in the circle
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(103, 114))]));
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);
        $this::assertCount(8, $session->getPendingResponseStore()->getAllPendingResponses());
        $this::assertNull($session['Q07.2.RESPONSE']);
        $this::assertEquals(0.0, $session['Q07.2.SCORE']->getValue());

        // Q07.3 - Incorrect and out of the circle
        $session->beginAttempt();
        $session->endAttempt(new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new QtiPoint(30, 13))]));
        $this::assertNull($session['Q07.3.RESPONSE']);
        $session->moveNext();

        $storage->persist($session);
        $session = $storage->retrieve($sessionId);

        // Response processing should have taken place beauce this is the end of the current test part.
        // The Pending Response Store should be then flushed and now empty.
        $this::assertCount(0, $session->getPendingResponseStore()->getAllPendingResponses());
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

    public function testNonLinear(): void
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

    public function testFiles(): void
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
        $this::assertFileExists($path1 = $session['Q01.RESPONSE']->getPath());
        $this::assertFileExists($path2 = $session['Q02.RESPONSE']->getPath());
        $this::assertFileExists($path3 = $session['Q03.RESPONSE']->getPath());

        $storage->delete($session);

        // -- Check files that files are removed from the file system after deletion of the session.
        $this::assertFileDoesNotExist($path1);
        $this::assertFileDoesNotExist($path2);
        $this::assertFileDoesNotExist($path3);
    }

    public function testTemplateProcessingBasic1(): void
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

    public function testTemplateDefault1(): void
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
        $this::assertNull($session['QTPL1.RESPONSE']);
        $this::assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());

        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // TPL1's response should still be at their default.
        $this::assertNull($session['QTPL1.RESPONSE']);

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
        $this::assertNull($session['QTPL2.RESPONSE']);

        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($sessionId);

        // TPL2's response should still be at their default.
        $this::assertNull($session['QTPL2.RESPONSE']);

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

    public function testVisitedTestPartsLinear1TestPart(): void
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

    public function testVisitedTestPartsLinear2TestPart(): void
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

    public function testVisitedTestPartsNonLinear3TestPartJumpBeginningOfTestPart(): void
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

    public function testVisitedTestPartsNonLinear3TestPartJumpMiddleOfTestPart(): void
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

    public function testVisitedTestPartsNonLinear3TestPartJumpEndOfTestPart(): void
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

    public function test300Items(): void
    {
        // Test to prove that a test with more than 255 items can be instantiated and handled.
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/route_count/300_items_long.xml');
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $session->beginTestSession();
        $sessionId = $session->getSessionId();

        // To start, persist and forget.
        $storage->persist($session);

        // Let's test!
        $expectedRouteCount = $test->getComponentsByClassName('assessmentItemRef')->count();

        // Let's perform 300 moveNext calls to reach the end of the test.
        for ($i = 0; $i < $expectedRouteCount; $i++) {
            $session = $storage->retrieve($sessionId);

            $position = $session->getRoute()->getPosition();
            $assessmentItemRefIdentifier = $session->getCurrentAssessmentItemRef()->getIdentifier();
            $this->assertEquals($i, $position);
            $this->assertEquals('Q' . ($i + 1), $assessmentItemRefIdentifier);

            $session->moveNext();
            $storage->persist($session);
        }

        $this->assertEquals(300, $i);
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
    }

    public function testConfigPersistence(): void
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

    public function testChibaSession()
    {
        $url = self::samplesDir() . 'custom/runtime/chiba-compact-test.xml';
        $doc = new XmlCompactDocument();
        $doc->load($url);
        $test = $doc->getDocumentComponent();
        $sessionManager = new SessionManager(new FileSystemFileManager());
        $storage = new LocalQtiBinaryStorage($sessionManager, $test);
        $session = $storage->instantiate();
        $sessionId = $session->getSessionId();

        $session->setTime(self::createDate('2024-01-22 09:00:00'));
        $session->beginTestSession();

        // item-2
        $this->assertEquals('item-2', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:00:20'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-3
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-3', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:00:22'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:00:30'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-4
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-4', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:00:31'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:00:40'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-11
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-11', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:00:41'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:01:10'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_s5')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-12
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-12', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:01:12'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:01:20'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::MULTIPLE,
                    BaseType::IDENTIFIER,
                    new MultipleContainer(
                        BaseType::IDENTIFIER,
                        [
                            new QtiIdentifier('choice_supermarket'),
                            new QtiIdentifier('choice_convenience'),
                            new QtiIdentifier('choice_school'),
                            new QtiIdentifier('choice_station'),
                            new QtiIdentifier('choice_park')
                        ]
                    )
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-13
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-13', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:01:23'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:01:30'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_2')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_6')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-34
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-34', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:01:31'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:01:40'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_star')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-35
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-35', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:01:42'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:02:02'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('あいうえお')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-21
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-21', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:02:03'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:02:15'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::MULTIPLE,
                    BaseType::DIRECTED_PAIR,
                    new MultipleContainer(
                        BaseType::DIRECTED_PAIR,
                        [
                            new QtiDirectedPair('gapimg_1', 'associablehotspot_1'),
                            new QtiDirectedPair('gapimg_2', 'associablehotspot_2'),
                            new QtiDirectedPair('gapimg_3', 'associablehotspot_3')
                        ]
                    )
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-22
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-22', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:02:16'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:02:23'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_1')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-6
        $this->assertEquals('item-6', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:02:24'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:02:30'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-7
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-7', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:02:31'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:02:40'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_2')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_7')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-5
        $this->assertEquals('item-5', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:02:41'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:02:52'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-8
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-8', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:02:53'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:03:11'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_2')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_5')
                ),
                new ResponseVariable(
                    'RESPONSE_2',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_11')
                ),
                new ResponseVariable(
                    'RESPONSE_3',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_7')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-14
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-14', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:03:12'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:03:20'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::MULTIPLE,
                    BaseType::IDENTIFIER,
                    new MultipleContainer(
                        BaseType::IDENTIFIER,
                        [
                            new QtiIdentifier('choice_D'),
                            new QtiIdentifier('choice_F')
                        ]
                    )
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-19
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-19', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:03:21'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:03:33'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_6')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-20
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-20', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:03:34'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:03:41'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_1')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_8')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-23
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-23', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:03:42'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:03:50'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_2')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_8')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-24
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-24', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:03:51'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:00'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_1')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_5')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-26
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-26', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:01'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:10'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE_3',
                    Cardinality::SINGLE,
                    BaseType::INTEGER,
                    new QtiInteger(1)
                ),
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_5')
                ),
                new ResponseVariable(
                    'RESPONSE_4',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('言葉だけじゃなく、グラフも入れてある')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('国民1人当たりのごみ処理費用')
                ),
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-27
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-27', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:11'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:22'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_4')
                ),
                new ResponseVariable(
                    'RESPONSE_1',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_8')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-36
        $this->assertEquals('item-36', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:23'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:25'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-1
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-1', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:26'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:32'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_1')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-16
        $this->assertEquals('item-16', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:33'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:40'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-17
        $this->assertEquals('item-17', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:41'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:04:52'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-9
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-9', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:04:53'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:05:04'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('あいこは、くだものがすきです。')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-29
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-29', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:05:05'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:05:20'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('あなたの妹は、左にいる子ですね。')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-30
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-30', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:05:21'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:05:30'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('雨の日は、室内で読書をします。')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-31
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-31', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:05:31'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:05:44'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('ぼくのクラスは、３ねん')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-32
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-32', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:05:45'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:06:00'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('もんしろちょうが、キャベツばたけにい')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-33
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-33', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:06:01'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:06:20'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE',
                    Cardinality::SINGLE,
                    BaseType::STRING,
                    new QtiString('さっかーで、Aちーむ')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-18
        $this->assertEquals('item-18', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:06:21'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:06:23'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-10
        $this->assertEquals('item-10', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:06:24'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:06:27'));
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-37
        $session = $storage->retrieve($sessionId);
        $this->assertEquals('item-37', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->setTime(self::createDate('2024-01-22 09:06:28'));
        $session->beginAttempt();
        $session->setTime(self::createDate('2024-01-22 09:06:53'));
        $session->endAttempt(
            new State([
                new ResponseVariable(
                    'RESPONSE_01',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_01_1')
                ),
                new ResponseVariable(
                    'RESPONSE_02',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_02_2')
                ),
                new ResponseVariable(
                    'RESPONSE_03',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_03_1')
                ),
                new ResponseVariable(
                    'RESPONSE_04',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_04_2')
                ),
                new ResponseVariable(
                    'RESPONSE_05',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_05_2')
                ),
                new ResponseVariable(
                    'RESPONSE_06',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_06_2')
                ),
                new ResponseVariable(
                    'RESPONSE_07',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_07_1')
                ),
                new ResponseVariable(
                    'RESPONSE_08',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_08_2')
                ),
                new ResponseVariable(
                    'RESPONSE_09',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_09_3')
                ),
                new ResponseVariable(
                    'RESPONSE_10',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_10_3')
                ),
                new ResponseVariable(
                    'RESPONSE_11',
                    Cardinality::SINGLE,
                    BaseType::IDENTIFIER,
                    new QtiIdentifier('choice_11_1')
                )
            ])
        );
        $session->moveNext();
        $storage->persist($session);

        // item-15
        $this->assertEquals('item-15', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();
        $storage->persist($session);

        // item-28
        $this->assertEquals('item-28', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();

        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $storage->persist($session);


        // Check data.
        $session = $storage->retrieve($sessionId);

        // item-2
        $this->assertEquals('PT20S', $session['item-2.duration']->__toString());

        // item-3
        $this->assertEquals('PT8S', $session['item-3.duration']->__toString());

        // item-4
        $this->assertEquals('PT9S', $session['item-4.duration']->__toString());

        // item-11
        $this->assertEquals('choice_s5', $session->getAssessmentItemSessions('item-11')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT29S', $session['item-11.duration']->__toString());

        // item-12
        $this->assertEquals(
            ['choice_supermarket', 'choice_convenience', 'choice_school', 'choice_station', 'choice_park'],
            $session->getAssessmentItemSessions('item-12')[0]['RESPONSE']->getArrayCopy()
        );
        $this->assertEquals('PT8S', $session['item-12.duration']->__toString());


        // item-13
        $this->assertEquals('choice_2', $session->getAssessmentItemSessions('item-13')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_6', $session->getAssessmentItemSessions('item-13')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT7S', $session['item-13.duration']->__toString());

        // item-34
        $this->assertEquals('choice_star', $session->getAssessmentItemSessions('item-34')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT9S', $session['item-34.duration']->__toString());

        // item-35
        $this->assertEquals('あいうえお', $session->getAssessmentItemSessions('item-35')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT20S', $session['item-35.duration']->__toString());

        // item-21
        $this->assertTrue($session->getAssessmentItemSessions('item-21')[0]['RESPONSE'][0]->equals(new QtiDirectedPair('gapimg_1', 'associablehotspot_1')));
        $this->assertTrue($session->getAssessmentItemSessions('item-21')[0]['RESPONSE'][1]->equals(new QtiDirectedPair('gapimg_2', 'associablehotspot_2')));
        $this->assertTrue($session->getAssessmentItemSessions('item-21')[0]['RESPONSE'][2]->equals(new QtiDirectedPair('gapimg_3', 'associablehotspot_3')));
        $this->assertEquals('PT12S', $session['item-21.duration']->__toString());

        // item-22
        $this->assertEquals('choice_1', $session->getAssessmentItemSessions('item-22')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT7S', $session['item-22.duration']->__toString());

        // item-6
        $this->assertEquals('PT6S', $session['item-6.duration']->__toString());

        // item-7
        $this->assertEquals('choice_2', $session->getAssessmentItemSessions('item-7')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_7', $session->getAssessmentItemSessions('item-7')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT9S', $session['item-7.duration']->__toString());

        // item-5
        $this->assertEquals('PT11S', $session['item-5.duration']->__toString());

        // item-8
        $this->assertEquals('choice_2', $session->getAssessmentItemSessions('item-8')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_5', $session->getAssessmentItemSessions('item-8')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT18S', $session['item-8.duration']->__toString());

        // item-14
        $this->assertTrue($session->getAssessmentItemSessions('item-14')[0]['RESPONSE'][0]->equals('choice_D'));
        $this->assertTrue($session->getAssessmentItemSessions('item-14')[0]['RESPONSE'][1]->equals('choice_F'));
        $this->assertEquals('PT8S', $session['item-14.duration']->__toString());

        // item-19
        $this->assertEquals('choice_6', $session->getAssessmentItemSessions('item-19')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT12S', $session['item-19.duration']->__toString());

        // item-20
        $this->assertEquals('choice_1', $session->getAssessmentItemSessions('item-20')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_8', $session->getAssessmentItemSessions('item-20')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT7S', $session['item-20.duration']->__toString());

        // item-23
        $this->assertEquals('choice_2', $session->getAssessmentItemSessions('item-23')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_8', $session->getAssessmentItemSessions('item-23')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT8S', $session['item-23.duration']->__toString());

        // item-24
        $this->assertEquals('choice_1', $session->getAssessmentItemSessions('item-24')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_5', $session->getAssessmentItemSessions('item-24')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT9S', $session['item-24.duration']->__toString());

        // item-26
        $this->assertEquals(1, $session->getAssessmentItemSessions('item-26')[0]['RESPONSE_3']->getValue());
        $this->assertEquals('choice_5', $session->getAssessmentItemSessions('item-26')[0]['RESPONSE']->getValue());
        $this->assertEquals('言葉だけじゃなく、グラフも入れてある', $session->getAssessmentItemSessions('item-26')[0]['RESPONSE_4']->getValue());
        $this->assertEquals('国民1人当たりのごみ処理費用', $session->getAssessmentItemSessions('item-26')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT9S', $session['item-26.duration']->__toString());

        // item-27
        $this->assertEquals('choice_4', $session->getAssessmentItemSessions('item-27')[0]['RESPONSE']->getValue());
        $this->assertEquals('choice_8', $session->getAssessmentItemSessions('item-27')[0]['RESPONSE_1']->getValue());
        $this->assertEquals('PT11S', $session['item-27.duration']->__toString());

        // item-36
        $this->assertEquals('PT2S', $session['item-36.duration']->__toString());

        // item-1
        $this->assertEquals('choice_1', $session->getAssessmentItemSessions('item-1')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT6S', $session['item-1.duration']->__toString());

        // item-16
        $this->assertEquals('PT7S', $session['item-16.duration']->__toString());

        // item-17
        $this->assertEquals('PT11S', $session['item-17.duration']->__toString());

        // item-9
        $this->assertEquals('あいこは、くだものがすきです。', $session->getAssessmentItemSessions('item-9')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT11S', $session['item-9.duration']->__toString());

        // item-29
        $this->assertEquals('あなたの妹は、左にいる子ですね。', $session->getAssessmentItemSessions('item-29')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT15S', $session['item-29.duration']->__toString());

        // item-30
        $this->assertEquals('雨の日は、室内で読書をします。', $session->getAssessmentItemSessions('item-30')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT9S', $session['item-30.duration']->__toString());

        // item-31
        $this->assertEquals('ぼくのクラスは、３ねん', $session->getAssessmentItemSessions('item-31')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT13S', $session['item-31.duration']->__toString());

        // item-32
        $this->assertEquals('もんしろちょうが、キャベツばたけにい', $session->getAssessmentItemSessions('item-32')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT15S', $session['item-32.duration']->__toString());

        // item-33
        $this->assertEquals('さっかーで、Aちーむ', $session->getAssessmentItemSessions('item-33')[0]['RESPONSE']->getValue());
        $this->assertEquals('PT19S', $session['item-33.duration']->__toString());

        // item-18
        $this->assertEquals('PT2S', $session['item-18.duration']->__toString());

        // item-10
        $this->assertEquals('PT3S', $session['item-10.duration']->__toString());

        // item-37
        $this->assertEquals('choice_01_1', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_01']->getValue());
        $this->assertEquals('choice_02_2', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_02']->getValue());
        $this->assertEquals('choice_03_1', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_03']->getValue());
        $this->assertEquals('choice_04_2', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_04']->getValue());
        $this->assertEquals('choice_05_2', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_05']->getValue());
        $this->assertEquals('choice_06_2', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_06']->getValue());
        $this->assertEquals('choice_07_1', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_07']->getValue());
        $this->assertEquals('choice_08_2', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_08']->getValue());
        $this->assertEquals('choice_09_3', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_09']->getValue());
        $this->assertEquals('choice_10_3', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_10']->getValue());
        $this->assertEquals('choice_11_1', $session->getAssessmentItemSessions('item-37')[0]['RESPONSE_11']->getValue());
        $this->assertEquals('PT25S', $session['item-37.duration']->__toString());
    }
}
