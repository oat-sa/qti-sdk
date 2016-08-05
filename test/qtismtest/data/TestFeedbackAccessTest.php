<?php
namespace qtismtest\data;

use qtismtest\QtiSmEnumTestCase;
use qtism\data\TestFeedbackAccess;

class TestFeedbackAccessTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return TestFeedbackAccess::class;
    }
    
    protected function getNames()
    {
        return array(
            'atEnd',
            'during'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'AT_END',
            'DURING'
        );
    }
    
    protected function getConstants()
    {
        return array(
            TestFeedbackAccess::AT_END,
            TestFeedbackAccess::DURING
        );
    }
}
