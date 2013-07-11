<?php

use qtism\data\AssessmentItemRef;

use qtism\data\SectionPartCollection;
use qtism\data\AssessmentSection;

require_once (dirname(__FILE__) . '/../../QtiSmTestCase.php');



class QtiComponentTest extends QtiSmTestCase {
	
	public function GetComponentByIdSimple() {
		$id = 'assessmentSection1';
		$title = 'Assessment Section Title';
		$assessmentSection = new AssessmentSection($id, $title, true);
		
		$sectionParts = new SectionPartCollection();
		$sectionParts[] = new AssessmentItemRef('Q01', './Q01.xml');
		$sectionParts[] = new AssessmentItemRef('Q02', './Q02.xml');
		$sectionParts[] = new AssessmentItemRef('Q03', './Q03.xml');
		$sectionParts[] = new AssessmentItemRef('Q04', './Q04.xml');
		$assessmentSection->setSectionParts($sectionParts);
		
		$search = $assessmentSection->getComponentByIdentifier('Q02');
		$this->assertSame($sectionParts[1], $search);
	}
	
	public function testGetComponentByIdComplex() {
		$id = 'assessmentSectionRoot';
		$title = 'Assessment Section Root';
		$assessmentSectionRoot = new AssessmentSection($id, $title, true);
		
		// -- subAssessmentSection1
		$id = 'subAssessmentSection1';
		$title = 'Sub-AssessmentSection 1';
		$subAssessmentSection1 = new AssessmentSection($id, $title, true);
		
		$sectionParts = new SectionPartCollection();
		$sectionParts[] = new AssessmentItemRef('Q01', './Q01.xml');
		$sectionParts[] = new AssessmentItemRef('Q02', './Q02.xml');
		$sectionParts[] = new AssessmentItemRef('Q03', './Q03.xml');
		$sectionParts[] = new AssessmentItemRef('Q04', './Q04.xml');
		$subAssessmentSection1->setSectionParts($sectionParts);
		
		// -- subAssessmentSection2
		$id = 'subAssessmentSection2';
		$title = 'Sub-AssessmentSection 1';
		$subAssessmentSection2 = new AssessmentSection($id, $title, true);
		
		$sectionParts = new SectionPartCollection();
		$sectionParts[] = new AssessmentItemRef('Q05', './Q05.xml');
		$sectionParts[] = new AssessmentItemRef('Q06', './Q06.xml');
		$sectionParts[] = new AssessmentItemRef('Q07', './Q07.xml');
		$subAssessmentSection2->setSectionParts($sectionParts);
		
		// -- bind the whole thing together.
		$sectionParts = new SectionPartCollection();
		$sectionParts[] = $subAssessmentSection1;
		$sectionParts[] = $subAssessmentSection2;
		$assessmentSectionRoot->setSectionParts($sectionParts);
		
		$search = $assessmentSectionRoot->getComponentByIdentifier('Q02');
		$this->assertEquals('Q02', $search->getIdentifier());
		
		$search = $assessmentSectionRoot->getComponentByIdentifier('Q04');
		$this->assertEquals('Q04', $search->getIdentifier());
		
		$search = $assessmentSectionRoot->getComponentByIdentifier('Q05');
		$this->assertEquals('Q05', $search->getIdentifier());
		
		$search = $assessmentSectionRoot->getComponentByIdentifier('Q07');
		$this->assertEquals('Q07', $search->getIdentifier());
		
		$search = $assessmentSectionRoot->getComponentByIdentifier('subAssessmentSection1');
		$this->assertEquals('subAssessmentSection1', $search->getIdentifier());
		
		$search = $assessmentSectionRoot->getComponentByIdentifier('subAssessmentSection2');
		$this->assertEquals('subAssessmentSection2', $search->getIdentifier());
	}
}