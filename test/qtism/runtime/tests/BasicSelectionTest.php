<?php

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\tests\BasicSelection;
use qtism\data\storage\xml\XmlAssessmentTestDocument;

class BasicSelectionTest extends QtiSmTestCase {
    
    public function testBasicSelection() {
        $doc = new XmlAssessmentTestDocument();
        $doc->load(self::samplesDir() . 'custom/selection_and_ordering.xml');
        
        $assessmentSection = $doc->getComponentByIdentifier('S01', true);
        $this->assertEquals('S01', $assessmentSection->getIdentifier());
        $selector = new BasicSelection($assessmentSection);
        
        $selectedAssessmentSection = $selector->select();
        $selectedSectionParts = $selectedAssessmentSection->getSectionParts();
        
        $this->assertEquals(1, count($selectedSectionParts));
        $this->assertFalse(isset($selectedSectionParts['S01A']) && isset($selectedSectionParts['S01B']));
        $this->assertTrue(isset($selectedSectionParts['S01A']) || isset($selectedSectionParts['S01B']));
        
        $selectedSectionIdentifier = (isset($selectedSectionParts['S01A'])) ? 'S01A' : 'S01B';
        $this->assertEquals(3, count($selectedSectionParts[$selectedSectionIdentifier]->getSectionParts()));
    }
}