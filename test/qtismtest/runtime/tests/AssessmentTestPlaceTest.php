<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmEnumTestCase;
use qtism\runtime\tests\AssessmentTestPlace;

class AssessmentTestPlaceTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return AssessmentTestPlace::class;
    }
    
    protected function getNames()
    {
        return array(
            'testPart',
            'assessmentSection',
            'assessmentItem',
            'assessmentTest'
        );
    }
    
    protected function getKeys()
    {
        return array(
            'TEST_PART',
            'ASSESSMENT_SECTION',
            'ASSESSMENT_ITEM',
            'ASSESSMENT_TEST'
        );
    }
    
    protected function getConstants()
    {
        return array(
            AssessmentTestPlace::TEST_PART,
            AssessmentTestPlace::ASSESSMENT_SECTION,
            AssessmentTestPlace::ASSESSMENT_ITEM,
            AssessmentTestPlace::ASSESSMENT_TEST
        );
    }
}
