<?php

namespace qtismtest\data;

use qtism\data\SubmissionMode;
use qtismtest\QtiSmEnumTestCase;

class SubmissionModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return SubmissionMode::class;
    }

    protected function getNames()
    {
        return [
            'individual',
            'simultaneous',
        ];
    }

    protected function getKeys()
    {
        return [
            'INDIVIDUAL',
            'SIMULTANEOUS',
        ];
    }

    protected function getConstants()
    {
        return [
            SubmissionMode::INDIVIDUAL,
            SubmissionMode::SIMULTANEOUS,
        ];
    }
}
