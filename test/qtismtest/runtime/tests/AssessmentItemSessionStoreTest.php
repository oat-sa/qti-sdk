<?php

namespace qtismtest\runtime\tests;

use qtism\data\ExtendedAssessmentItemRef;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionStore;
use qtismtest\QtiSmAssessmentItemTestCase;

/**
 * Class AssessmentItemSessionStoreTest
 */
class AssessmentItemSessionStoreTest extends QtiSmAssessmentItemTestCase
{
    public function testHasMultipleOccurences(): void
    {
        $itemRef1 = new ExtendedAssessmentItemRef('Q01', './Q01.xml');
        $store = new AssessmentItemSessionStore();

        // No session registered for $itemRef1.
        $this::assertFalse($store->hasMultipleOccurences($itemRef1));

        // A single session registered for $itemRef1.
        $session = $this->createAssessmentItemSession($itemRef1);
        $store->addAssessmentItemSession($session, 0);
        $this::assertFalse($store->hasMultipleOccurences($itemRef1));

        // Two session registered for $itemRef1.
        $session = $this->createAssessmentItemSession($itemRef1);
        $store->addAssessmentItemSession($session, 1);
        $this::assertTrue($store->hasMultipleOccurences($itemRef1));

        $this::assertTrue($store->hasAssessmentItemSession($itemRef1, 0));
        $this::assertFalse($store->hasAssessmentItemSession(new ExtendedAssessmentItemRef('Q02', './Q02.xml')));
    }

    public function testGetAllAssessmentItemSessions(): void
    {
        $itemRef1 = new ExtendedAssessmentItemRef('Q01', './Q01.xml');
        $itemRef2 = new ExtendedAssessmentItemRef('Q02', './Q02.xml');
        $itemRef3 = new ExtendedAssessmentItemRef('Q03', './Q03.xml');

        $store = new AssessmentItemSessionStore();
        $store->addAssessmentItemSession($this->createAssessmentItemSession($itemRef1), 0);
        $store->addAssessmentItemSession($this->createAssessmentItemSession($itemRef1), 1);
        $store->addAssessmentItemSession($this->createAssessmentItemSession($itemRef1), 3);
        $this::assertCount(3, $store->getAllAssessmentItemSessions());

        $store->addAssessmentItemSession($this->createAssessmentItemSession($itemRef2), 0);
        $store->addAssessmentItemSession($this->createAssessmentItemSession($itemRef3), 0);
        $this::assertCount(5, $store->getAllAssessmentItemSessions());
    }
}
