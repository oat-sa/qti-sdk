<?php
namespace qtismtest\runtime\tests;

use qtismtest\QtiSmAssessmentTestSessionTestCase;

class AssessmentTestSessionSelectionAndOrderingTest extends QtiSmAssessmentTestSessionTestCase
{    
    public function testSelectionAndOrderingWithReplacement()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_with_replacement.xml');
        $this->assertEquals(50, $assessmentTestSession->getRouteCount());
    }

    public function testSelectionAndOrderingOverflow()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_overflow.xml');
        $this->assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasic()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_basic.xml');
        $this->assertEquals(3, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasicFixed()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_basic_fixed.xml');
        $this->assertEquals(5, $assessmentTestSession->getRouteCount());
        $this->assertEquals('Q2', $assessmentTestSession->getRoute()->getRouteItemAt(1)->getAssessmentItemRef()->getIdentifier());
    }

    public function testOrderingVisible()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_visible.xml');
        $this->assertEquals(9, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleDontKeepTogether()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_invisible_dont_keep_together.xml');
        $this->assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleKeepTogether()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_invisible_keep_together.xml');
        $this->assertEquals(12, $assessmentTestSession->getRouteCount());
    }
}
