<?php

declare(strict_types=1);

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
    protected function getEnumerationFqcn(): string
    {
        return TestFeedbackAccess::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'atEnd',
            'during',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'AT_END',
            'DURING',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            TestFeedbackAccess::AT_END,
            TestFeedbackAccess::DURING,
        ];
    }
}
