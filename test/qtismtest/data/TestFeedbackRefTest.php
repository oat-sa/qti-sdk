<?php

declare(strict_types=1);

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\data\ShowHide;
use qtism\data\TestFeedbackAccess;
use qtism\data\TestFeedbackRef;
use qtismtest\QtiSmTestCase;

/**
 * Class TestFeedbackRefTest
 */
class TestFeedbackRefTest extends QtiSmTestCase
{
    public function testSetAccessWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the TestFeedbackAccess enumeration.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', true, ShowHide::SHOW, 'ref.xml');
    }

    public function testSetShowHideWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the ShowHide enumeration.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, true, 'ref.xml');
    }

    public function testSetOutcomeIdentifierWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 999, TestFeedbackAccess::DURING, ShowHide::SHOW, 'ref.xml');
    }

    public function testSetIdentifierWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedbackRef = new TestFeedbackRef(999, 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, ShowHide::SHOW, 'ref.xml');
    }

    public function testSetHrefWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'' is not a valid URI.");

        $testFeedbackRef = new TestFeedbackRef('IDENTIFIER', 'OUTCOMEIDENTIFIER', TestFeedbackAccess::DURING, ShowHide::SHOW, '');
    }
}
