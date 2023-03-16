<?php

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2023 (original work) Open Assessment Technologies SA;
 */

declare(strict_types=1);

namespace qtismtest\runtime\storage\serializable;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;
use qtism\common\datatypes\files\FileSystemFileManager;
use qtism\common\datatypes\QtiDirectedPair;
use qtism\common\datatypes\QtiDuration;
use qtism\common\datatypes\QtiFloat;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiInteger;
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
use qtism\runtime\storage\common\AssessmentTestSeeker;
use qtism\runtime\storage\driver\FilesystemLocalDriver;
use qtism\runtime\storage\serializable\PhpSerializer;
use qtism\runtime\storage\serializable\QtiSerializableStorage;
use qtism\runtime\tests\AssessmentItemSessionState;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\runtime\tests\AssessmentTestSessionState;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;

class QtSerializableWithLocalFilesystemDriver extends QtiSmTestCase
{
    public function testLocalQtiBinaryStorage(): void
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/itemsubset.xml');
        $test = $doc->getDocumentComponent();

        $sessionManager = new SessionManager(new FileSystemFileManager());
        $seeker = new AssessmentTestSeeker($test, [
            'assessmentItemRef',
            'assessmentSection',
            'testPart',
            'outcomeDeclaration',
            'responseDeclaration',
            'templateDeclaration',
            'branchRule',
            'preCondition',
            'itemSessionControl',
        ]);
        $localStorageDriver = new FilesystemLocalDriver();
        $serializer = new PhpSerializer($sessionManager, $test, $seeker);
        $storage = new QtiSerializableStorage($sessionManager, $test, $serializer, $localStorageDriver);
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

}
