<?php

namespace qtismtest\data;

use qtism\data\AssessmentSection;
use qtism\data\rules\Ordering;
use qtismtest\QtiSmTestCase;

class AssessmentSectionTest extends QtiSmTestCase
{
    public function testSetTitleWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Title must be a string, 'integer' given."
        );
        new AssessmentSection('S01', 999, true);
    }

    public function testSetVisibleWrongType()
    {
        $this->setExpectedException(
            '\\InvalidArgumentException',
            "Visible must be a boolean, 'integer' given."
        );
        new AssessmentSection('S01', 'Section 01', 1);
    }

    public function testSetKeepTogetherWrongType()
    {
        $section = new AssessmentSection('S01', 'Section 01', true);

        $this->setExpectedException(
            '\\InvalidArgumentException',
            "KeepTogether must be a boolean, 'integer' given."
        );

        $section->setKeepTogether(1);
    }

    public function testHasOrdering()
    {
        $section = new AssessmentSection('S01', 'Section 01', true);
        $section->setOrdering(new Ordering(true));
        $this->assertTrue($section->hasOrdering());
    }
}
