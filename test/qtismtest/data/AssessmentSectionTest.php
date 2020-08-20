<?php

namespace qtismtest\data;

use qtism\data\AssessmentSection;
use qtism\data\rules\Ordering;
use qtismtest\QtiSmTestCase;

class AssessmentSectionTest extends QtiSmTestCase
{
    public function testSetTitleWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Title must be a string, 'integer' given.");
        new AssessmentSection('S01', 999, true);
    }

    public function testSetVisibleWrongType()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("Visible must be a boolean, 'integer' given.");
        new AssessmentSection('S01', 'Section 01', 1);
    }

    public function testSetKeepTogetherWrongType()
    {
        $section = new AssessmentSection('S01', 'Section 01', true);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("KeepTogether must be a boolean, 'integer' given.");

        $section->setKeepTogether(1);
    }

    public function testHasOrdering()
    {
        $section = new AssessmentSection('S01', 'Section 01', true);
        $section->setOrdering(new Ordering(true));
        $this->assertTrue($section->hasOrdering());
    }
}
