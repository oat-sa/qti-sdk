<?php

namespace qtismtest\data;

use InvalidArgumentException;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use qtism\data\SectionPartCollection;
use qtism\data\TestPart;
use qtismtest\QtiSmTestCase;

/**
 * Class TestPartTest
 */
class TestPartTest extends QtiSmTestCase
{
    public function testCreateInvalidIdentifier(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("'999' is not a valid QTI Identifier.");

        $testPart = new TestPart(
            '999',
            new AssessmentSectionCollection([new AssessmentSection('S01', 'Section 01', true)])
        );
    }

    public function testCreateNotEnoughAssessmentSections(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A TestPart must contain at least one AssessmentSection.');

        $testPart = new TestPart(
            'T01',
            new AssessmentSectionCollection()
        );
    }

    public function testCreateWrongSectionTypes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('A TestPart contain only contain AssessmentSection or AssessmentSectionRef objects.');

        $testPart = new TestPart(
            'T01',
            new SectionPartCollection([new AssessmentItemRef('Q01', 'Q01.xml')])
        );
    }
}
