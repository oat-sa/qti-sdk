<?php

declare(strict_types=1);

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\data\content\FlowStaticCollection;
use qtism\data\TestFeedback;
use qtismtest\QtiSmTestCase;

/**
 * Class TestFeedbackTest
 */
class TestFeedbackTest extends QtiSmTestCase
{
    public function testSetAccessWrongType(): void
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the TestFeedbackAccess enumeration.");

        $testFeedback->setAccess(true);
    }

    public function testSetOutcomeIdentifierWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedback = new TestFeedback('IDENTIFIER', 999, new FlowStaticCollection());
    }

    public function testSetShowHideWrongType(): void
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the ShowHide enumeration.");

        $testFeedback->setShowHide(true);
    }

    public function testSetIdentifierWrongType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedback = new TestFeedback(999, 'OUTCOMEIDENTIFIER', new FlowStaticCollection());
    }

    public function testSetTitleWrongType(): void
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Title must be a string, 'integer' given.");

        $testFeedback->setTitle(999);
    }
}
