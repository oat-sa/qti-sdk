<?php

namespace qtismtest\data;

use qtismtest\QtiSmTestCase;
use qtism\data\TestPart;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\SectionPartCollection;
use qtism\data\AssessmentItemRef;

class TestPartTest extends QtiSmTestCase
{
    public function testCreateInvalidIdentifier()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "'999' is not a valid QTI Identifier."
        );

        $testPart = new TestPart(
            '999',
            new AssessmentSectionCollection([new AssessmentSection('S01', 'Section 01', true)])
        );
    }
    
    public function testCreateNotEnoughAssessmentSections()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "A TestPart must contain at least one AssessmentSection."
        );

        $testPart = new TestPart(
            'T01',
            new AssessmentSectionCollection()
        );
    }
    
    public function testCreateWrongSectionTypes()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "A TestPart contain only contain AssessmentSection or AssessmentSectionRef objects."
        );

        $testPart = new TestPart(
            'T01',
            new SectionPartCollection([new AssessmentItemRef('Q01', 'Q01.xml')])
        );
    }
}
