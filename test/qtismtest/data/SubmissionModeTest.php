<?php

namespace qtismtest\data;

use qtism\data\SubmissionMode;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class SubmissionModeTest
 */
class SubmissionModeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn(): string
    {
        return SubmissionMode::class;
    }

    /**
     * @return array
     */
    protected function getNames(): array
    {
        return [
            'individual',
            'simultaneous',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys(): array
    {
        return [
            'INDIVIDUAL',
            'SIMULTANEOUS',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants(): array
    {
        return [
            SubmissionMode::INDIVIDUAL,
            SubmissionMode::SIMULTANEOUS,
        ];
    }
}
