<?php

namespace qtismtest\data;

use qtism\data\TestFeedbackAccess;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class TestFeedbackAccessTest
 */
class TestFeedbackAccessTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return TestFeedbackAccess::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'atEnd',
            'during',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'AT_END',
            'DURING',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            TestFeedbackAccess::AT_END,
            TestFeedbackAccess::DURING,
        ];
    }
}
