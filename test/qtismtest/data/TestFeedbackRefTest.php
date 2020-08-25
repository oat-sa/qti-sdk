<?php

namespace qtismtest\data;

use qtism\data\ShowHide;
use qtism\data\TestFeedbackAccess;
use qtism\data\TestFeedbackRef;
use qtismtest\QtiSmTestCase;

/**
 * Class TestFeedbackRefTest
 *
 * @package qtismtest\data
 */
class TestFeedbackRefTest extends QtiSmTestCase
{
    public function testSetAccessWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the TestFeedbackAccess enumeration.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', true, ShowHide::SHOW, 'ref.xml');
    }

    public function testSetShowHideWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the ShowHide enumeration.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, true, 'ref.xml');
    }

    public function testSetOutcomeIdentifierWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 999, TestFeedbackAccess::DURING, ShowHide::SHOW, 'ref.xml');
    }

    public function testSetIdentifierWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedbackRef = new TestFeedbackRef(999, 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, ShowHide::SHOW, 'ref.xml');
    }

    public function testSetHrefWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid URI.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, ShowHide::SHOW, 999);
    }
}
