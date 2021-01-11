<?php

namespace qtismtest\runtime\tests;

use qtism\common\datatypes\QtiBoolean;
use qtism\common\datatypes\QtiInteger;
use qtism\common\enums\BaseType;
use qtism\common\enums\Cardinality;
use qtism\data\AssessmentItemRef;
use qtism\runtime\common\ResponseVariable;
use qtism\runtime\common\State;
use qtism\runtime\tests\PendingResponses;
use qtism\runtime\tests\PendingResponseStore;
use qtismtest\QtiSmTestCase;

/**
 * Class PendingResponseStoreTest
 */
class PendingResponseStoreTest extends QtiSmTestCase
{
    public function testPendingResponseStore()
    {
        $itemRef1 = new AssessmentItemRef('Q01', './Q01.xml');
        $itemRef2 = new AssessmentItemRef('Q02', './Q02.xml');
        $itemRef3 = new AssessmentItemRef('Q03', './Q02.xml');

        $state1 = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(true))]);
        $state2 = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::BOOLEAN, new QtiBoolean(false))]);
        $state3 = new State([new ResponseVariable('RESPONSE', Cardinality::SINGLE, BaseType::INTEGER, new QtiInteger(1337))]);

        $store = new PendingResponseStore();
        $store->addPendingResponses(new PendingResponses($state1, $itemRef1));
        $store->addPendingResponses(new PendingResponses($state2, $itemRef1, 1));

        $this::assertCount(2, $store->getAllPendingResponses());

        $this::assertTrue($store->hasPendingResponses($itemRef1));
        $this::assertFalse($store->hasPendingResponses($itemRef3));
        $this::assertFalse($store->hasPendingResponses($itemRef1, 4));

        $this::assertSame($itemRef1, $store->getPendingResponses($itemRef1)->getAssessmentItemRef());
        $this::assertSame($state2, $store->getPendingResponses($itemRef1, 1)->getState());
    }
}
