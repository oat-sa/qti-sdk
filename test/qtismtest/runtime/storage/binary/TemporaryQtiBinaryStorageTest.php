<?php
namespace qtismtest\runtime\storage\binary;

use qtism\runtime\tests\AssessmentTestSession;

use qtismtest\QtiSmTestCase;
use qtism\runtime\storage\binary\BinaryAssessmentTestSeeker;
use qtism\common\datatypes\files\FileSystemFile;
use qtism\common\datatypes\Duration;
use qtism\common\datatypes\Identifier;
use qtism\runtime\tests\SessionManager;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\common\datatypes\Pair;
use qtism\common\datatypes\DirectedPair;
use qtism\common\datatypes\Point;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\common\MultipleContainer;
use qtism\data\QtiComponentIterator;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\storage\binary\TemporaryQtiBinaryStorage;
use \DateTime;
use \DateTimeZone;

class TemporaryQtiBinaryStorageTest extends QtiSmTestCase {
    
    public function testTemporaryQtiBinaryStorage() {
    
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $test = $doc->getDocumentComponent();
    
        $sessionManager = new SessionManager();
        $storage = new TemporaryQtiBinaryStorage($sessionManager, new BinaryAssessmentTestSeeker($doc->getDocumentComponent()));
        $session = $storage->instantiate($test);
        $sessionId = $session->getSessionId();
    
        $this->assertInstanceOf('qtism\\runtime\\tests\\AssessmentTestSession', $session);
        $this->assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());
    
        // The candidate begins the test session at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginTestSession();
        
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
    
        // The test session has begun. We are in linear mode so that all
        // item sessions must be initialized and selected for presentation
        // to the candidate.
        $itemSessionStore = $session->getAssessmentItemSessionStore();
        $itemSessionCount = 0;
        $iterator = new QtiComponentIterator($doc->getDocumentComponent(), array('assessmentItemRef'));
        foreach ($iterator as $itemRef) {
            $refItemSessions = $itemSessionStore->getAssessmentItemSessions($itemRef);
            foreach ($refItemSessions as $refItemSession) {
                $this->assertEquals(AssessmentItemSessionState::INITIAL, $refItemSession->getState());
                $itemSessionCount++;
            }
        }
        $this->assertEquals(9, $itemSessionCount);
    
        // The outcome variables composing the test-level global scope
        // must be set with their default value if any.
        foreach ($doc->getDocumentComponent()->getOutcomeDeclarations() as $outcomeDeclaration) {
            $this->assertFalse(is_null($session[$outcomeDeclaration->getIdentifier()]));
            $this->assertEquals(0, $session[$outcomeDeclaration->getIdentifier()]->getValue());
        }
    
        
        // S01 -> Q01 - Correct response.
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q01.scoring']);
        $this->assertEquals(0.0, $session['Q01.scoring']->getValue());
        $this->assertSame(null, $session['Q01.RESPONSE']);
    
        // The candidate begins the attempt on Q01 at 13:00:00.
        $session->setTime(new DateTime('2014-07-14T13:00:00+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The canditate spends 1 second on item Q01.
        $session->setTime(new DateTime('2014-07-14T13:00:01+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        
        // Are durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        
        $session->moveNext();
    
        // Because Q01 is not a multi-occurence item in the route, isLastOccurenceUpdate always return false.
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
    
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // After the persist, do we still have the correct scores and durations for Q01?.
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q01.scoring']);
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());
        $this->assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
    
        
        // S01 -> Q02 - Incorrect response.
        $this->assertEquals('Q02', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S01', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());
        
        // The candidate begins an attempt on Q02 at 13:00:02.
        $session->setTime(new DateTime('2014-07-14T13:00:02+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 2 seconds on item Q02.
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('C', 'M')))))));
        
        // Whate about scores of Q02?
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q02.SCORE']);
        $this->assertEquals(1.0, $session['Q02.SCORE']->getValue());
        
        // Are the durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT4S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT4S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT4S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        
        $session->moveNext();
    
        
        // S01 -> Q03 - Skip.
        // The candidate begins an attempt on Q03 at 13:00:04.
        $session->setTime(new DateTime('2014-07-14T13:00:04+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 10 seconds on Q03 and then skip the item.
        $session->setTime(new DateTime('2014-07-14T13:00:14+00:00', new DateTimeZone('UTC')));
        $session->skip();
        
        // Are the durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        
        // !!! We move to the next section S02.
        $session->moveNext();
    
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
    
        
        // S02 -> Q04 - Correct response.
        $this->assertEquals('Q04', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals('S02', $session->getCurrentAssessmentSection()->getIdentifier());
        $this->assertEquals('P01', $session->getCurrentTestPart()->getIdentifier());
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
        
        // The candidate begins an attempt on Q04 at 13:00:15.
        $session->setTime(new DateTime('2014-07-14T13:00:15+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 5 seconds on Q04.
        $session->setTime(new DateTime('2014-07-14T13:00:20+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::DIRECTED_PAIR, new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'G1'), new DirectedPair('Su', 'G2')))))));
        
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // What about score of Q04.
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q04.SCORE']);
        $this->assertEquals(3.0, $session['Q04.SCORE']->getValue());
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertTrue($session['Q04.RESPONSE']->equals(new MultipleContainer(BaseType::DIRECTED_PAIR, array(new DirectedPair('W', 'G1'), new DirectedPair('Su', 'G2')))));
        
        // Are the durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT20S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT20S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT6S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q04.duration']->equals(new Duration('PT5S')));
        
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $session->moveNext();

        
        // S02 -> Q05 - Skip.
        // The candidate begins the attempt on Q05 at 13:00:20.
        $session->setTime(new DateTime('2014-07-14T13:00:20+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 1 second on Q05.
        $session->setTime(new DateTime('2014-07-14T13:00:21+00:00', new DateTimeZone('UTC')));
        $session->skip();
        
        // Are the durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT21S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT21S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT7S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q04.duration']->equals(new Duration('PT5S')));
        $this->assertTrue($session['Q05.duration']->equals(new Duration('PT1S')));
        
        // !!! We move to the next section S03.
        $session->moveNext();
    
        
        // Q06 - Skip.
        // The candidate begins the attempt on Q06 at 13:00:24.
        $session->setTime(new DateTime('2014-07-14T13:00:24+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 2 seconds on Q06.
        $session->setTime(new DateTime('2014-07-14T13:00:26+00:00', new DateTimeZone('UTC')));
        $session->skip();
        
        // Are the durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT26S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT26S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT12S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT0S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q04.duration']->equals(new Duration('PT5S')));
        $this->assertTrue($session['Q05.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q06.duration']->equals(new Duration('PT2S')));
        
        // !!! We move to the next section S03.
        $session->moveNext();
    
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
    
        
        // S03 -> Q07.1 - Incorrect response (but inside the circle).
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $session->getCurrentAssessmentItemRefOccurence());
        
        // The candidate begins an attempt on Q07.1 at 13:00:28.
        $session->setTime(new DateTime('2014-07-14T13:00:28+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends 10 seconds on Q07.1.
        $session->setTime(new DateTime('2014-07-14T13:00:38+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(103, 114)))));
        
        // Are the durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT38S')));
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT38S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT12S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT12S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q04.duration']->equals(new Duration('PT5S')));
        $this->assertTrue($session['Q05.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q06.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q07.1.duration']->equals(new Duration('PT10S')));
        
        $session->moveNext();
    
        // We now test the lastOccurence update for this multi-occurence item.
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        
        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));
    
        
        // S03 -> Q07.2 - Incorrect response (outside the circle).
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $session->getCurrentAssessmentItemRefOccurence());
        
        // The candidate begins the attempt on Q07.2 at 13:00:38.
        $session->setTime(new DateTime('2014-07-14T13:00:38+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate spends a whole minute on Q07.2.
        $session->setTime(new DateTime('2014-07-14T13:01:38+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(200, 200)))));
        
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT98S'))); // NO FEAR!
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT1M38S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT12S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT1M12S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q04.duration']->equals(new Duration('PT5S')));
        $this->assertTrue($session['Q05.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q06.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q07.1.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q07.2.duration']->equals(new Duration('PT1M')));
        
        $session->moveNext();
    
        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));

        // A little bit of noisy persistence...
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);

        $this->assertFalse($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 0));
        $this->assertTrue($session->isLastOccurenceUpdate($session->getCurrentAssessmentItemRef(), 1));
    
        
        // S03 -> Q07.3 - Correct response (perfectly on the point).
        $this->assertEquals('Q07', $session->getCurrentAssessmentItemRef()->getIdentifier());
        $this->assertEquals(2, $session->getCurrentAssessmentItemRefOccurence());
        
        // The candidate takes an attempt on Q07.3 at 13:01:39
        $session->setTime(new DateTime('2014-07-14T13:01:39+00:00', new DateTimeZone('UTC')));
        $session->beginAttempt();
        
        // The candidate takes an hour (yes, an hour) to respond on Q07.3.
        $session->setTime(new DateTime('2014-07-14T14:01:39+00:00', new DateTimeZone('UTC')));
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))));
        $session->moveNext();
    
        // -- End of test, outcome processing performed correctly?
        
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Integer', $session['NCORRECTS01']);
        $this->assertEquals(1, $session['NCORRECTS01']->getValue());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Integer', $session['NCORRECTS02']);
        $this->assertEquals(1, $session['NCORRECTS02']->getValue());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Integer', $session['NCORRECTS03']);
        $this->assertEquals(1, $session['NCORRECTS03']->getValue());
        $this->assertEquals(6, $session['NINCORRECT']->getValue());
        $this->assertEquals(6, $session['NRESPONSED']->getValue());
        $this->assertEquals(9, $session['NPRESENTED']->getValue());
        $this->assertEquals(9, $session['NSELECTED']->getValue());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['PERCENT_CORRECT']);
        $this->assertEquals(round(33.33333, 3), round($session['PERCENT_CORRECT']->getValue(), 3));
        
        // -- End of test, are durations correct?
        $this->assertTrue($session['itemsubset.duration']->equals(new Duration('PT3699S'))); // NO FEAR!
        $this->assertTrue($session['P01.duration']->equals(new Duration('PT1H1M39S')));
        $this->assertTrue($session['S01.duration']->equals(new Duration('PT14S')));
        $this->assertTrue($session['S02.duration']->equals(new Duration('PT12S')));
        $this->assertTrue($session['S03.duration']->equals(new Duration('PT1H1M13S')));
        $this->assertTrue($session['Q01.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q02.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q03.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q04.duration']->equals(new Duration('PT5S')));
        $this->assertTrue($session['Q05.duration']->equals(new Duration('PT1S')));
        $this->assertTrue($session['Q06.duration']->equals(new Duration('PT2S')));
        $this->assertTrue($session['Q07.1.duration']->equals(new Duration('PT10S')));
        $this->assertTrue($session['Q07.2.duration']->equals(new Duration('PT1M')));
        $this->assertTrue($session['Q07.3.duration']->equals(new Duration('PT1H')));
    }
    
    public function testLinearNavigationSimultaneousSubmission() {
    
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset_simultaneous.xml');
        $test = $doc->getDocumentComponent();
        
        $factory = new SessionManager($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($factory, new BinaryAssessmentTestSeeker($doc->getDocumentComponent()));
        $sessionId = 'linearSimultaneous1337';
        $session = $storage->instantiate($doc->getDocumentComponent(), $sessionId);
        $session->beginTestSession();
    
        // Nothing in pending responses. The test has just begun.
        $this->assertEquals(0, count($session->getPendingResponseStore()->getAllPendingResponses()));
    
        // Q01 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA')))));
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(1, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertEquals(null, $session['Q01.RESPONSE']);
        $this->assertEquals(0.0, $session['Q01.scoring']->getValue());
    
        // Q02 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::MULTIPLE, BaseType::PAIR, new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('C', 'M'), new Pair('D', 'L')))))));
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertSame(null, $session['Q02.RESPONSE']);
        $this->assertEquals(0.0, $session['Q02.SCORE']->getValue());
        $this->assertEquals(2, count($session->getPendingResponseStore()->getAllPendingResponses()));
    
        // Q03 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(3, count($session->getPendingResponseStore()->getAllPendingResponses()));
    
        // Q04 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(4, count($session->getPendingResponseStore()->getAllPendingResponses()));
    
        // Q05 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(5, count($session->getPendingResponseStore()->getAllPendingResponses()));
    
        // Q06 - Skip
        $session->beginAttempt();
        $session->endAttempt(new State());
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(6, count($session->getPendingResponseStore()->getAllPendingResponses()));
    
        // Q07.1 - Correct
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(102, 113)))));
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(7, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertSame(null, $session['Q07.1.RESPONSE']);
        $this->assertEquals(0.0, $session['Q07.1.SCORE']->getValue());
    
        // Q07.2 - Incorrect but in the circle
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(103, 114)))));
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertEquals(8, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertSame(null, $session['Q07.2.RESPONSE']);
        $this->assertEquals(0.0, $session['Q07.2.SCORE']->getValue());
    
        // Q07.3 - Incorrect and out of the circle
        $session->beginAttempt();
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::POINT, new Point(30, 13)))));
        $this->assertSame(null, $session['Q07.3.RESPONSE']);
        $session->moveNext();
    
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // Response processing should have taken place beauce this is the end of the current test part.
        // The Pending Response Store should be then flushed and now empty.
        $this->assertEquals(0, count($session->getPendingResponseStore()->getAllPendingResponses()));
        $this->assertEquals(0.0, $session['Q07.3.SCORE']->getValue());
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // Let's check the overall Assessment Test Session state.
        $this->assertInstanceOf('qtism\\common\\datatypes\\Identifier', $session['Q01.RESPONSE']);
        $this->assertEquals('ChoiceA', $session['Q01.RESPONSE']->getValue());
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q01.scoring']);
        $this->assertEquals(1.0, $session['Q01.scoring']->getValue());
        
        $this->assertTrue($session['Q02.RESPONSE']->equals(new MultipleContainer(BaseType::PAIR, array(new Pair('A', 'P'), new Pair('C', 'M'), new Pair('D', 'L')))));
        $this->assertEquals(4.0, $session['Q02.SCORE']->getValue());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q03.SCORE']);
        $this->assertEquals(0.0, $session['Q03.SCORE']->getValue());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q04.SCORE']);
        $this->assertEquals(0.0, $session['Q04.SCORE']->getValue());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q05.SCORE']);
        $this->assertEquals(0.0, $session['Q05.SCORE']->getValue());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q06.mySc0r3']);
        $this->assertEquals(0.0, $session['Q06.mySc0r3']->getValue());
        
        $this->assertTrue($session['Q07.1.RESPONSE']->equals(new Point(102, 113)));
        $this->assertEquals(1.0, $session['Q07.1.SCORE']->getValue());
        
        $this->assertTrue($session['Q07.2.RESPONSE']->equals(new Point(103, 114)));
        $this->assertEquals(1.0, $session['Q07.2.SCORE']->getValue());
        
        $this->assertTrue($session['Q07.3.RESPONSE']->equals(new Point(30, 13)));
        $this->assertInstanceOf('qtism\\common\\datatypes\\Float', $session['Q07.3.SCORE']);
        $this->assertEquals(0.0, $session['Q07.3.SCORE']->getValue());
        
        $this->assertEquals(2, $session['NCORRECTS01']->getValue());
        $this->assertEquals(0, $session['NCORRECTS02']->getValue());
        $this->assertEquals(1, $session['NCORRECTS03']->getValue());
        $this->assertEquals(6, $session['NINCORRECT']->getValue());
        $this->assertEquals(5, $session['NRESPONSED']->getValue());
        $this->assertEquals(9, $session['NPRESENTED']->getValue());
        $this->assertEquals(9, $session['NSELECTED']->getValue());
        $this->assertEquals(round(33.33333, 3), round($session['PERCENT_CORRECT']->getValue(), 3));
    }
    
    public function testNonLinear() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/jumps.xml');
        $test = $doc->getDocumentComponent();
        
        $sessionManager = new SessionManager($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($sessionManager, new BinaryAssessmentTestSeeker($test));
        $session = $storage->instantiate($test);
        $session->beginTestSession();
        $sessionId = $session->getSessionId();
        
        $storage->persist($session);
        $session = $storage->retrieve($test, $sessionId);
    }
    
    public function testFiles() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/files/files.xml');
        $test = $doc->getDocumentComponent();
        
        $sessionManager = new SessionManager($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($sessionManager, new BinaryAssessmentTestSeeker($test));
        $session = $storage->instantiate($test);
        $session->beginTestSession();
        $sessionId = $session->getSessionId();
        
        // --- Q01 - files_1.txt = ('text.txt', 'text/plain', 'Some text...')
        $session->beginAttempt();
        $filepath = self::samplesDir() . 'datatypes/file/files_1.txt';
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, FileSystemFile::retrieveFile($filepath)))));
        $session->moveNext();
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q01.RESPONSE']);
        $this->assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this->assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this->assertEquals('Some text...', $session['Q01.RESPONSE']->getData());
        
        // Let's persist and retrieve and look if we have the same value in Q01.RESPONSE.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q01.RESPONSE']);
        $this->assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this->assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this->assertEquals('Some text...', $session['Q01.RESPONSE']->getData());
        
        // --- Q02 - files_2.txt = ('', 'text/html', '<img src="/qtism/img.png"/>')
        $session->beginAttempt();
        $filepath = self::samplesDir() . 'datatypes/file/files_2.txt';
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, FileSystemFile::retrieveFile($filepath)))));
        $session->moveNext();
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q02.RESPONSE']);
        $this->assertEquals('', $session['Q02.RESPONSE']->getFilename());
        $this->assertEquals('text/html', $session['Q02.RESPONSE']->getMimeType());
        $this->assertEquals('<img src="/qtism/img.png"/>', $session['Q02.RESPONSE']->getData());
        
        // Again, we persist and retrieve.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // We now test all the collected variables.
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q01.RESPONSE']);
        $this->assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this->assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this->assertEquals('Some text...', $session['Q01.RESPONSE']->getData());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q02.RESPONSE']);
        $this->assertEquals('', $session['Q02.RESPONSE']->getFilename());
        $this->assertEquals('text/html', $session['Q02.RESPONSE']->getMimeType());
        $this->assertEquals('<img src="/qtism/img.png"/>', $session['Q02.RESPONSE']->getData());
        
        // --- Q03 - files_3.txt ('empty.txt', 'text/plain', '')
        $session->beginAttempt();
        $filepath = self::samplesDir() . 'datatypes/file/files_3.txt';
        $session->endAttempt(new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::FILE, FileSystemFile::retrieveFile($filepath)))));
        $session->moveNext();
        $this->assertFalse($session->isRunning());
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q02.RESPONSE']);
        $this->assertEquals('empty.txt', $session['Q03.RESPONSE']->getFilename());
        $this->assertEquals('text/plain', $session['Q03.RESPONSE']->getMimeType());
        $this->assertEquals('', $session['Q03.RESPONSE']->getData());
        
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // Final big check.
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q01.RESPONSE']);
        $this->assertEquals('text.txt', $session['Q01.RESPONSE']->getFilename());
        $this->assertEquals('text/plain', $session['Q01.RESPONSE']->getMimeType());
        $this->assertEquals('Some text...', $session['Q01.RESPONSE']->getData());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q02.RESPONSE']);
        $this->assertEquals('', $session['Q02.RESPONSE']->getFilename());
        $this->assertEquals('text/html', $session['Q02.RESPONSE']->getMimeType());
        $this->assertEquals('<img src="/qtism/img.png"/>', $session['Q02.RESPONSE']->getData());
        
        $this->assertInstanceOf('qtism\\common\\datatypes\\File', $session['Q02.RESPONSE']);
        $this->assertEquals('empty.txt', $session['Q03.RESPONSE']->getFilename());
        $this->assertEquals('text/plain', $session['Q03.RESPONSE']->getMimeType());
        $this->assertEquals('', $session['Q03.RESPONSE']->getData());
    }
    
    public function testTemplateProcessingBasic1() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/templates/template_processing_test_simple.xml');
        $test = $doc->getDocumentComponent();
        
        $sessionManager = new SessionManager($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($sessionManager, new BinaryAssessmentTestSeeker($test));
        $session = $storage->instantiate($test);
        $sessionId = $session->getSessionId();
        
        // Let's try to persist a not begun session.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // The session is instantiated, but not yet begun.
        $this->assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());
        
        // The session begins...
        $session->beginTestSession();
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
        
        // We are in linear, non adaptive test. In this context, all item sessions
        // should be already begun.
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];
        
        // The default values should be correctly initialized within their respective item sessions...
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this->assertEquals(1.0, $QTPL1Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(0.0, $QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(1.0, $QTPL1Session['GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());
        
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];
        
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this->assertEquals(2.0, $QTPL2Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(-1.0, $QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(2.0, $QTPL2Session['GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this->assertEquals(-1.0, $QTPL2Session['WRONGSCORE']->getValue());
        
        // Now let's make sure the persistence works correctly when templating is in force...
        // We do this by testing again that default values are correctly initialized within their respective
        // item sessions...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this->assertEquals(1.0, $QTPL1Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(0.0, $QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(1.0, $QTPL1Session['GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());
        
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];
        
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this->assertEquals(2.0, $QTPL2Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(-1.0, $QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(2.0, $QTPL2Session['GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this->assertEquals(-1.0, $QTPL2Session['WRONGSCORE']->getValue());
        
        // It seems to be ok! Let's take the test!
        $session->beginAttempt();
        // TPL1's responses should be applied their default values if any at the
        // beginning of the first attempt.
        $this->assertEquals('ChoiceB', $session['QTPL1.RESPONSE']->getValue());
        
        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // TPL1's response should still be at their default.
        $this->assertEquals('ChoiceB', $session['QTPL1.RESPONSE']->getValue());
        
        // -- TPL1 - Correct response.
        $candidateResponses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA'))));
        $session->endAttempt($candidateResponses);
        
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
        
        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $this->assertEquals('ChoiceA', $session['QTPL1.RESPONSE']->getValue());
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
        
        $session->moveNext();
        
        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // -- TPL2 - Correct response.
        $session->beginAttempt();
        
        // TPL2's responses should be at their default values if any at
        // the beginning of the first attempt.
        $this->assertEquals('ChoiceA', $session['QTPL2.RESPONSE']->getValue());
        
        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        // TPL2's response should still be at their default.
        $this->assertEquals('ChoiceA', $session['QTPL2.RESPONSE']->getValue());
        
        // -- TPL2 - Incorrect response.
        $candidateResponses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceC'))));
        $session->endAttempt($candidateResponses);
        
        $this->assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());
        
        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
        
        $this->assertEquals('ChoiceC', $session['QTPL2.RESPONSE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());
        
        // -- Go to the end of test.
        $session->moveNext();
        
        // Check states...
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];
        
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $QTPL1Session->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $QTPL2Session->getState());
    }
    
    public function testTemplateDefault1() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/templates/template_test_simple.xml');
        $test = $doc->getDocumentComponent();
    
        $sessionManager = new SessionManager($doc->getDocumentComponent());
        $storage = new TemporaryQtiBinaryStorage($sessionManager, new BinaryAssessmentTestSeeker($test));
        $session = $storage->instantiate($test);
        $sessionId = $session->getSessionId();
    
        // Let's try to persist a not begun session.
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // The session is instantiated, but not yet begun.
        $this->assertEquals(AssessmentTestSessionState::INITIAL, $session->getState());
    
        // The session begins...
        $session->beginTestSession();
        $this->assertEquals(AssessmentTestSessionState::INTERACTING, $session->getState());
    
        // We are in linear, non adaptive test. In this context, all item sessions
        // should be already begun.
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];
    
        // The the session is correctly instantiated, with the <templateDefault>s in force.
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this->assertEquals(1.0, $QTPL1Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(0.0, $QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(1.0, $QTPL1Session['GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());
    
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];
    
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this->assertEquals(2.0, $QTPL2Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(-1.0, $QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(2.0, $QTPL2Session['GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this->assertEquals(-1.0, $QTPL2Session['WRONGSCORE']->getValue());
    
        // Now let's make sure the persistence works correctly when <templateDefault>s are in force...
        // We do this by testing again that default values are correctly initialized within their respective
        // item sessions...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL1Session->getState());
        $this->assertEquals(1.0, $QTPL1Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(0.0, $QTPL1Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(1.0, $session['QTPL1.GOODSCORE']->getValue());
        $this->assertEquals(1.0, $QTPL1Session['GOODSCORE']->getValue());
        $this->assertEquals(0.0, $session['QTPL1.WRONGSCORE']->getValue());
        $this->assertEquals(0.0, $QTPL1Session['WRONGSCORE']->getValue());
    
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];
    
        $this->assertEquals(AssessmentItemSessionState::INITIAL, $QTPL2Session->getState());
        $this->assertEquals(2.0, $QTPL2Session->getVariable('GOODSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(-1.0, $QTPL2Session->getVariable('WRONGSCORE')->getDefaultValue()->getValue());
        $this->assertEquals(2.0, $session['QTPL2.GOODSCORE']->getValue());
        $this->assertEquals(2.0, $QTPL2Session['GOODSCORE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.WRONGSCORE']->getValue());
        $this->assertEquals(-1.0, $QTPL2Session['WRONGSCORE']->getValue());
    
        // It seems to be ok! Let's take the test!
        $session->beginAttempt();
        // TPL1's responses should be applied their default values if any at the
        // beginning of the first attempt.
        $this->assertEquals(null, $session['QTPL1.RESPONSE']);
    
        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // TPL1's response should still be at their default.
        $this->assertEquals(null, $session['QTPL1.RESPONSE']);
    
        // -- TPL1 - Correct response.
        $candidateResponses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceA'))));
        $session->endAttempt($candidateResponses);
    
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
    
        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        $this->assertEquals('ChoiceA', $session['QTPL1.RESPONSE']->getValue());
        $this->assertEquals(1.0, $session['QTPL1.SCORE']->getValue());
    
        $session->moveNext();
    
        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // -- TPL2 - Correct response.
        $session->beginAttempt();
    
        // TPL2's responses should be at their default values if any at
        // the beginning of the first attempt.
        $this->assertEquals(null, $session['QTPL2.RESPONSE']);
    
        // Noisy persistence ...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        // TPL2's response should still be at their default.
        $this->assertEquals(null, $session['QTPL2.RESPONSE']);
    
        // -- TPL2 - Incorrect response.
        $candidateResponses = new State(array(new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::IDENTIFIER, new Identifier('ChoiceC'))));
        $session->endAttempt($candidateResponses);
    
        $this->assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());
    
        // Noisy persistence...
        $storage->persist($session);
        unset($session);
        $session = $storage->retrieve($test, $sessionId);
    
        $this->assertEquals('ChoiceC', $session['QTPL2.RESPONSE']->getValue());
        $this->assertEquals(-1.0, $session['QTPL2.SCORE']->getValue());
    
        // -- Go to the end of test.
        $session->moveNext();
    
        // Check states...
        $QTPL1Sessions = $session->getAssessmentItemSessions('QTPL1');
        $QTPL1Session = $QTPL1Sessions[0];
        $QTPL2Sessions = $session->getAssessmentItemSessions('QTPL2');
        $QTPL2Session = $QTPL2Sessions[0];
    
        $this->assertEquals(AssessmentTestSessionState::CLOSED, $session->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $QTPL1Session->getState());
        $this->assertEquals(AssessmentItemSessionState::CLOSED, $QTPL2Session->getState());
    }
}