<?php

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\data\ItemSessionControl;
use qtismtest\QtiSmTestCase;

/**
 * Class ItemSessionControlTest
 */
class ItemSessionControlTest extends QtiSmTestCase
{
    public function testIsDefault(): void
    {
        $itemSessionControl = new ItemSessionControl();
        $this::assertTrue($itemSessionControl->isDefault());

        $itemSessionControl->setMaxAttempts(0);
        $this::assertFalse($itemSessionControl->isDefault());
    }

    public function testSetMaxAttemptsWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("MaxAttempts must be an integer, 'boolean' given.");

        $itemSessionControl->setMaxAttempts(true);
    }

    public function testSetShowFeedbackWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("ShowFeedback must be a boolean, 'integer' given.");

        $itemSessionControl->setShowFeedback(999);
    }

    public function testSetAllowReviewWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("AllowReview must be a boolean, 'integer' given.");

        $itemSessionControl->setAllowReview(999);
    }

    public function testSetShowSolutionWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("ShowSolution must be a boolean, 'integer' given.");

        $itemSessionControl->setShowSolution(999);
    }

    public function testSetAllowCommentWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("AllowComment must be a boolean, 'integer' given.");

        $itemSessionControl->setAllowComment(999);
    }

    public function testSetAllowSkippingWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("AllowSkipping must be a boolean, 'integer' given.");

        $itemSessionControl->setAllowSkipping(999);
    }

    public function testSetValidateResponsesWrongType(): void
    {
        $itemSessionControl = new ItemSessionControl();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("ValidateResponses must be a boolean value, 'integer' given.");

        $itemSessionControl->setValidateResponses(999);
    }
}
