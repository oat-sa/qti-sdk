<?php

namespace qtismtest\runtime\tests;

use qtism\data\storage\xml\XmlCompactDocument;
use qtism\runtime\tests\SessionManager;
use qtismtest\QtiSmTestCase;

/**
 * Class AssessmentTestSessionSelectionAndOrderingTest
 */
class AssessmentTestSessionSelectionAndOrderingTest extends QtiSmTestCase
{
    public function testSelectionAndOrdering()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_with_replacement.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(50, $assessmentTestSession->getRouteCount());
    }

    public function testSelectionAndOrderingOverflow()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_overflow.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasic()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_basic.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(3, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasicFixed()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_basic_fixed.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(5, $assessmentTestSession->getRouteCount());
        $this::assertEquals('Q2', $assessmentTestSession->getRoute()->getRouteItemAt(1)->getAssessmentItemRef()->getIdentifier());
    }

    public function testOrderingVisible()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_visible.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(9, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleDontKeepTogether()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_invisible_dont_keep_together.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleKeepTogether()
    {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_ordering/ordering_invisible_keep_together.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this::assertEquals(12, $assessmentTestSession->getRouteCount());
    }
}
