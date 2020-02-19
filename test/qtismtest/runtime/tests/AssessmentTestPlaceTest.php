<?php

namespace qtismtest\runtime\tests;

use qtism\runtime\tests\AssessmentTestPlace;
use qtismtest\QtiSmEnumTestCase;

class AssessmentTestPlaceTest extends QtiSmEnumTestCase
{
    protected function getEnumerationFqcn()
    {
        return AssessmentTestPlace::class;
    }

    protected function getNames()
    {
        return [
            'testPart',
            'assessmentSection',
            'assessmentItem',
            'assessmentTest',
        ];
    }

    protected function getKeys()
    {
        return [
            'TEST_PART',
            'ASSESSMENT_SECTION',
            'ASSESSMENT_ITEM',
            'ASSESSMENT_TEST',
        ];
    }

    protected function getConstants()
    {
        return [
            AssessmentTestPlace::TEST_PART,
            AssessmentTestPlace::ASSESSMENT_SECTION,
            AssessmentTestPlace::ASSESSMENT_ITEM,
            AssessmentTestPlace::ASSESSMENT_TEST,
        ];
    }
}
