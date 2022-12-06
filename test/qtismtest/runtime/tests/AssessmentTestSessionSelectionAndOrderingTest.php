<?php

namespace qtismtest\runtime\tests;

use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmAssessmentTestSessionTestCase;

/**
 * Class AssessmentTestSessionSelectionAndOrderingTest
 */
class AssessmentTestSessionSelectionAndOrderingTest extends QtiSmAssessmentTestSessionTestCase
{
    public function testSelectionAndOrderingWithReplacement(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_with_replacement.xml');
        $this::assertEquals(50, $assessmentTestSession->getRouteCount());
    }

    public function testSelectionAndOrderingOverflow(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_overflow.xml');
        $this::assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasic(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_basic.xml');
        $this::assertEquals(3, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasicFixed(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_basic_fixed.xml');
        $this::assertEquals(5, $assessmentTestSession->getRouteCount());
        $this::assertEquals('Q2', $assessmentTestSession->getRoute()->getRouteItemAt(1)->getAssessmentItemRef()->getIdentifier());
    }

    public function testOrderingVisible(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_visible.xml');
        $this::assertEquals(9, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleDontKeepTogether(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_invisible_dont_keep_together.xml');
        $this::assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleKeepTogether(): void
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_invisible_keep_together.xml');
        $this::assertEquals(12, $assessmentTestSession->getRouteCount());
    }
}
