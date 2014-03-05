<?php
use qtism\data\content\RubricBlockRef;

use qtism\common\datatypes\Duration;

use qtism\data\TimeLimits;

use qtism\data\ItemSessionControl;

use qtism\runtime\tests\routing\AssessmentItemOccurence;

use qtism\data\AssessmentItemRef;
use qtism\runtime\tests\routing\Step;
use qtism\runtime\tests\routing\AssessmentSection;
use qtism\runtime\tests\routing\AssessmentSectionCollection;
use qtism\runtime\tests\routing\TestPart;
use qtism\runtime\tests\routing\AssessmentTest;

require_once (dirname(__FILE__) . '/../../../../QtiSmTestCase.php');

class StepTest extends QtiSmTestCase {
    
    public function testInstantiation() {
        $test = new AssessmentTest('test1', 'Test 1');
        $part = new TestPart('T1');
        $sections = new AssessmentSectionCollection(array(new AssessmentSection('S1', 'Section 1')));
        
        $itemRef = new AssessmentItemRef('Q1', 'Q1.xml');
        $itemOcc = new AssessmentItemOccurence($itemRef);
        
        $step = new Step($test, $part, $sections, $itemOcc);
        $this->assertInstanceOf('qtism\\runtime\\tests\\routing\Step', $step);
        
        $this->assertSame($test, $step->getAssessmentTest());
        $this->assertSame($part, $step->getTestPart());
        $this->assertSame($sections, $step->getAssessmentSections());
        $this->assertEquals(1, count($step->getAssessmentSections()));
        $this->assertSame($itemOcc, $step->getAssessmentItemOccurence());
        $this->assertSame($itemRef, $step->getAssessmentItemOccurence()->getAssessmentItemRef());
        
        $this->assertEquals(0, count($step->getBranchRules()));
        $this->assertEquals(0, count($step->getPreConditions()));
        $this->assertEquals(0, count($step->getRubricBlockRefs()));
        $this->assertEquals(0, count($step->getTimeLimits()));
    }
    
    public function testGetItemSessionControl() {
        $test = new AssessmentTest('test1', 'Test 1');
        $part = new TestPart('T1');
        $sections = new AssessmentSectionCollection(array(new AssessmentSection('S1', 'Section1')));
        
        $itemRef = new AssessmentItemRef('Q1', 'Q1.xml');
        $itemOcc = new AssessmentItemOccurence($itemRef);
        
        $step = new Step($test, $part, $sections, $itemOcc);
        $stepControl = $step->getItemSessionControl();
        
        $this->assertSame(null, $stepControl);
        
        $testControl = new ItemSessionControl();
        $test->setItemSessionControl($testControl);
        $this->assertSame($test, $step->getItemSessionControl()->getOwner());
        $this->assertSame($testControl, $step->getItemSessionControl()->getItemSessionControl());
        
        // overrides test level control.
        $partControl = new ItemSessionControl();
        $part->setItemSessionControl($partControl);
        $this->assertSame($part, $step->getItemSessionControl()->getOwner());
        $this->assertSame($partControl, $step->getItemSessionControl()->getItemSessionControl());
        
        // overrides testPart level control.
        $sectionControl = new ItemSessionControl();
        $sections[0]->setItemSessionControl($sectionControl);
        $this->assertSame($sections[0], $step->getItemSessionControl()->getOwner());
        $this->assertSame($sectionControl, $step->getItemSessionControl()->getItemSessionControl());
        
        // overrides assessmentSection level control.
        $itemControl = new ItemSessionControl();
        $itemRef->setItemSessionControl($itemControl);
        $this->assertSame($itemOcc, $step->getItemSessionControl()->getOwner());
        $this->assertSame($itemControl, $step->getItemSessionControl()->getItemSessionControl());
    }
    
    public function testGetTimeLimits() {
        $test = new AssessmentTest('test1', 'Test 1');
        $part = new TestPart('T1');
        $sections = new AssessmentSectionCollection(array(new AssessmentSection('S1', 'Section1')));
        
        $itemRef = new AssessmentItemRef('Q1', 'Q1.xml');
        $itemOcc = new AssessmentItemOccurence($itemRef);
        
        $step = new Step($test, $part, $sections, $itemOcc);
        $stepTimeLimits = $step->getTimeLimits();
        
        // No timeLimits for the moment.
        $this->assertEquals(0, count($stepTimeLimits));
        
        // first stack at the test level.
        $testTimeLimits = new TimeLimits(null, new Duration('PT2M'));
        $test->setTimeLimits($testTimeLimits);
        $stepTimeLimits = $step->getTimeLimits();
        $this->assertEquals(1, count($stepTimeLimits));
        $this->assertSame($test, $stepTimeLimits[0]->getOwner());
        $this->assertSame($testTimeLimits, $stepTimeLimits[0]->getTimeLimits());
        
        // second stack at testPart level.
        $testPartTimeLimits = new TimeLimits(null, new Duration('PT3M'));
        $part->setTimeLimits($testPartTimeLimits);
        $stepTimeLimits = $step->getTimeLimits();
        $this->assertEquals(2, count($stepTimeLimits));
        $this->assertSame($part, $stepTimeLimits[1]->getOwner());
        $this->assertSame($testPartTimeLimits, $stepTimeLimits[1]->getTimeLimits());
        
        // third stack at assessmentSection level.
        $sectionTimeLimits = new TimeLimits(null, new Duration('PT4M'));
        $sections[0]->setTimeLimits($sectionTimeLimits);
        $stepTimeLimits = $step->getTimeLimits();
        $this->assertEquals(3, count($stepTimeLimits));
        $this->assertSame($sections[0], $stepTimeLimits[2]->getOwner());
        $this->assertSame($sectionTimeLimits, $stepTimeLimits[2]->getTimeLimits());
        
        // fourth stack at assessmentItem level.
        $itemTimeLimits = new TimeLimits(null, new Duration('PT5M'));
        $itemRef->setTimeLimits($itemTimeLimits);
        $stepTimeLimits = $step->getTimeLimits();
        $this->assertEquals(4, count($stepTimeLimits));
        $this->assertSame($itemOcc, $stepTimeLimits[3]->getOwner());
        $this->assertSame($itemTimeLimits, $stepTimeLimits[3]->getTimeLimits());
    }
    
    public function testGetRubricBlockRefs() {
        $test = new AssessmentTest('test1', 'Test 1');
        $part = new TestPart('T1');
        $section1 = new AssessmentSection('S1', 'Section1');
        $section1a = new AssessmentSection('S1A', 'Section1A');
        $sections = new AssessmentSectionCollection(array($section1, $section1a));
        
        $itemRef = new AssessmentItemRef('Q1', 'Q1.xml');
        $itemOcc = new AssessmentItemOccurence($itemRef);
        
        $step = new Step($test, $part, $sections, $itemOcc);
        
        // No rubrics at all.
        $rubrics = $step->getRubricBlockRefs();
        $this->assertEquals(0, count($rubrics));
        
        // Add a rubric to the top section (S1).
        $rb1 = new RubricBlockRef('RB1', 'RB1.xml');
        $sections[0]->addRubricBlockRef($rb1);
        $rubrics = $step->getRubricBlockRefs();
        $this->assertEquals(1, count($rubrics));
        $this->assertSame($rb1, $rubrics['RB1']);
        
        // Add a second rubric to the top section (S1).
        $rb2 = new RubricBlockRef('RB2', 'RB2.xml');
        $sections[0]->addRubricBlockRef($rb2);
        $rubrics = $step->getRubricBlockRefs();
        $this->assertEquals(2, count($rubrics));
        $this->assertSame($rb2, $rubrics['RB2']);
        
        // Add a rubric to the bottom section (S1A).
        $rb3 = new RubricBlockRef('RB3', 'RB3.xml');
        $sections[1]->addRubricBlockRef($rb3);
        $rubrics = $step->getRubricBlockRefs();
        $this->assertEquals(3, count($rubrics));
        $this->assertSame($rb3, $rubrics['RB3']);
    }
}