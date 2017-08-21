<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class AssessmentTestSessionSelectionAndOrdering extends QtiSmTestCase
{
    public function testSelectionAndOrdering() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/selection_and_ordering_with_replacement.xml');
        
        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this->assertEquals(50, $assessmentTestSession->getRouteCount());
    }
    
    public function testSelectionAndOrderingOverflow()
    {
        $assessmentTestSession = self::instantiate(self::samplesDir() . 'custom/runtime/selection_ordering/selection_and_ordering_overflow.xml');
        $this->assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasic() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/ordering_basic.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this->assertEquals(3, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingBasicFixed() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/ordering_basic_fixed.xml');
        
        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this->assertEquals(5, $assessmentTestSession->getRouteCount());
        $this->assertEquals('Q2', $assessmentTestSession->getRoute()->getRouteItemAt(1)->getAssessmentItemRef()->getIdentifier());
    }

    public function testOrderingVisible() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/ordering_visible.xml');
         
        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this->assertEquals(9, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleDontKeepTogether() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/ordering_invisible_dont_keep_together.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this->assertEquals(12, $assessmentTestSession->getRouteCount());
    }

    public function testOrderingInvisibleKeepTogether() {
        $doc = new XmlCompactDocument();
        $doc->load(self::samplesDir() . 'custom/runtime/ordering_invisible_keep_together.xml');

        $sessionManager = new SessionManager();
        $assessmentTestSession = $sessionManager->createAssessmentTestSession($doc->getDocumentComponent());
        $this->assertEquals(12, $assessmentTestSession->getRouteCount());
    }
}
