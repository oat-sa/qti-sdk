<?php

namespace qtismtest\data;

use qtism\data\TestFeedbackAccess;
use qtismtest\QtiSmEnumTestCase;

class TestFeedbackAccessTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TestFeedbackAccess::class;
    }

    protected function getNames()
    {
        return [
            'atEnd',
            'during',
        ];
    }

    protected function getKeys()
    {
        return [
            'AT_END',
            'DURING',
        ];
    }

    protected function getConstants()
    {
        return [
            TestFeedbackAccess::AT_END,
            TestFeedbackAccess::DURING,
        ];
    }
}
