<?php

namespace qtismtest\data;

use qtism\data\SubmissionMode;
use qtismtest\QtiSmEnumTestCase;

/**
 * Class SubmissionModeTest
 *
 * @package qtismtest\data
 */
class SubmissionModeTest extends QtiSmEnumTestCase
{
    /**
     * @return string
     */
    protected function getEnumerationFqcn()
    {
        return SubmissionMode::class;
    }

    /**
     * @return array
     */
    protected function getNames()
    {
        return [
            'individual',
            'simultaneous',
        ];
    }

    /**
     * @return array
     */
    protected function getKeys()
    {
        return [
            'INDIVIDUAL',
            'SIMULTANEOUS',
        ];
    }

    /**
     * @return array
     */
    protected function getConstants()
    {
        return [
            SubmissionMode::INDIVIDUAL,
            SubmissionMode::SIMULTANEOUS,
        ];
    }
}
