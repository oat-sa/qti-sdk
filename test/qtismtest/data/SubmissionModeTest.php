<?php
namespace qtismtest\data;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\SubmissionMode;

class SubmissionModeTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return SubmissionMode::class;
    }
    
    protected function getNames()
    {
        return array(
            'individual',
            'simultaneous'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'INDIVIDUAL',
            'SIMULTANEOUS'
        );
    }
    
    protected function getConstants()
    {
        return array(
            SubmissionMode::INDIVIDUAL,
            SubmissionMode::SIMULTANEOUS
        );
    }
}
