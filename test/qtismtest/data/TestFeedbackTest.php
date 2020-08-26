<?php

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\data\content\FlowStaticCollection;
use qtism\data\TestFeedback;
use qtismtest\QtiSmTestCase;

/**
 * Class TestFeedbackTest
 *
 * @package qtismtest\data
 */
class TestFeedbackTest extends QtiSmTestCase
{
    public function testSetAccessWrongType()
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the TestFeedbackAccess enumeration.");

        $testFeedback->setAccess(true);
    }

    public function testSetOutcomeIdentifierWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedback = new TestFeedback('IDENTIFIER', 999, new FlowStaticCollection());
    }

    public function testSetShowHideWrongType()
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'1' is not a value from the ShowHide enumeration.");

        $testFeedback->setShowHide(true);
    }

    public function testSetIdentifierWrongType()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testFeedback = new TestFeedback(999, 'OUTCOMEIDENTIFIER', new FlowStaticCollection());
    }

    public function testSetTitleWrongType()
    {
        $testFeedback = new TestFeedback('IDENTIFIER', 'OUTCOMEIDENTIFIER', new FlowStaticCollection());

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Title must be a string, 'integer' given.");

        $testFeedback->setTitle(999);
    }
}
