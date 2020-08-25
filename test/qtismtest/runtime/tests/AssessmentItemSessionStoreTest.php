<?php

namespace qtismtest\runtime\tests;

use qtism\data\ExtendedAssessmentItemRef;
use qtism\runtime\tests\AssessmentItemSession;
use qtism\runtime\tests\AssessmentItemSessionStore;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentItemSessionStoreTest
 *
 * @package qtismtest\runtime\tests
 */
class AssessmentItemSessionStoreTest extends QtiSmTestCase
{
    public function testHasMultipleOccurences()
    {
        $itemRef1 = new ExtendedAssessmentItemRef('Q01', './Q01.xml');
        $store = new AssessmentItemSessionStore();

        // No session registered for $itemRef1.
        $this->assertFalse($store->hasMultipleOccurences($itemRef1));

        // A single session registered for $itemRef1.
        $session = new AssessmentItemSession($itemRef1);
        $store->addAssessmentItemSession($session, 0);
        $this->assertFalse($store->hasMultipleOccurences($itemRef1));

        // Two session registered for $itemRef1.
        $session = new AssessmentItemSession($itemRef1);
        $store->addAssessmentItemSession($session, 1);
        $this->assertTrue($store->hasMultipleOccurences($itemRef1));

        $this->assertTrue($store->hasAssessmentItemSession($itemRef1, 0));
        $this->assertFalse($store->hasAssessmentItemSession(new ExtendedAssessmentItemRef('Q02', './Q02.xml')));
    }

    public function testGetAllAssessmentItemSessions()
    {
        $itemRef1 = new ExtendedAssessmentItemRef('Q01', './Q01.xml');
        $itemRef2 = new ExtendedAssessmentItemRef('Q02', './Q02.xml');
        $itemRef3 = new ExtendedAssessmentItemRef('Q03', './Q03.xml');

        $store = new AssessmentItemSessionStore();
        $store->addAssessmentItemSession(new AssessmentItemSession($itemRef1), 0);
        $store->addAssessmentItemSession(new AssessmentItemSession($itemRef1), 1);
        $store->addAssessmentItemSession(new AssessmentItemSession($itemRef1), 3);
        $this->assertEquals(3, count($store->getAllAssessmentItemSessions()));

        $store->addAssessmentItemSession(new AssessmentItemSession($itemRef2), 0);
        $store->addAssessmentItemSession(new AssessmentItemSession($itemRef3), 0);
        $this->assertEquals(5, count($store->getAllAssessmentItemSessions()));
    }
}
