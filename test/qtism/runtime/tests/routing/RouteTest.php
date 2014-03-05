<?php

require_once (dirname(__FILE__) . '/../../../../QtiSmAltRouteTestCase.php');

use qtism\runtime\tests\routing\StepCollection;
use qtism\runtime\tests\routing\AssessmentSectionCollection;
use qtism\runtime\tests\routing\Step;
use qtism\runtime\tests\routing\AssessmentItemOccurence;
use qtism\runtime\tests\routing\AssessmentSection;
use qtism\runtime\tests\routing\TestPart;
use qtism\runtime\tests\routing\AssessmentTest;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;
use qtism\common\collections\IdentifierCollection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItem;
use qtism\runtime\tests\routing\Route;
use qtism\data\TestPartCollection;
use qtism\data\AssessmentItemRefCollection;

class AlternateRouteTest extends QtiSmAltRouteTestCase {
    
    public function testRouteTest() {
        
        $assessmentTest = new AssessmentTest('test', 'A Test');
        $testPart = new TestPart('TP1');
        $section1 = new AssessmentSection('S1', 'Section 1', true);
        $section2 = new AssessmentSection('S2', 'Section 2' , true);
        
        $q = new AssessmentItemRef('Q1', 'Q1.xml');
        $q->setCategories(new IdentifierCollection(array('mathematics', 'expert')));
        $q1 = new AssessmentItemOccurence($q);
        $step1 = new Step($assessmentTest, $testPart, new AssessmentSectionCollection(array($section1)), $q1);
        
        $q = new AssessmentItemRef('Q2', 'Q2.xml');
        $q->setCategories(new IdentifierCollection(array('sciences', 'expert')));
        $q2 = new AssessmentItemOccurence($q);
        $step2 = new Step($assessmentTest, $testPart, new AssessmentSectionCollection(array($section1)), $q2);
        
        $q = new AssessmentItemRef('Q3', 'Q3.xml');
        $q->setCategories(new IdentifierCollection(array('mathematics')));
        $q3 = new AssessmentItemOccurence($q);
        $step3 = new Step($assessmentTest, $testPart, new AssessmentSectionCollection(array($section1)), $q3);
        
        $q = new AssessmentItemRef('Q4', 'Q4.xml');
        $q4 = new AssessmentItemOccurence($q);
        $step4 = new Step($assessmentTest, $testPart, new AssessmentSectionCollection(array($section1)), $q4);
        
        $q = new AssessmentItemRef('Q5', 'Q5.xml');
        $q5 = new AssessmentItemOccurence($q);
        $step5 = new Step($assessmentTest, $testPart, new AssessmentSectionCollection(array($section2)), $q5);
        
        $q = new AssessmentItemRef('Q6', 'Q6.xml');
        $q->setCategories(new IdentifierCollection(array('mathematics')));
        $q6 = new AssessmentItemOccurence($q);
        $step6 = new Step($assessmentTest, $testPart, new AssessmentSectionCollection(array($section2)), $q6);
        
        $route = new Route();
        $route->addStep($step1);
        $route->addStep($step2);
        $route->addStep($step3);
        $route->addStep($step4);
        $route->addStep($step5);
        $route->addStep($step6);
        
        // Is Q3 in TP1?
        $this->assertTrue($route->isInTestPart(2, $testPart));
        
        // What are the RouteItem objects involved in each AssessmentItemRef ?
        $involved = $route->getStepsByAssessmentItemRef($q1->getAssessmentItemRef());
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q1.0', $involved[0]->getAssessmentItemOccurence()->getIdentifier());
        
        $involved = $route->getStepsByAssessmentItemRef($q2->getAssessmentItemRef());
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q2.0', $involved[0]->getAssessmentItemOccurence()->getIdentifier());
        
        $involved = $route->getStepsByAssessmentItemRef($q3->getAssessmentItemRef());
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q3.0', $involved[0]->getAssessmentItemOccurence()->getIdentifier());
        
        $involved = $route->getStepsByAssessmentItemRef($q4->getAssessmentItemRef());
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q4.0', $involved[0]->getAssessmentItemOccurence()->getIdentifier());
        
        $involved = $route->getStepsByAssessmentItemRef($q5->getAssessmentItemRef());
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q5.0', $involved[0]->getAssessmentItemOccurence()->getIdentifier());
        
        $involved = $route->getStepsByAssessmentItemRef($q6->getAssessmentItemRef());
        $this->assertEquals(1, count($involved));
        $this->assertEquals('Q6.0', $involved[0]->getAssessmentItemOccurence()->getIdentifier());
        
        // What are the Step objects involded in part 'TP1'?
        $tp1Steps = $route->getStepsByTestPart($testPart);
        $this->assertEquals(6, count($tp1Steps));
        $tp1Steps = $route->getStepsByTestPart('TP1');
        $this->assertEquals(6, count($tp1Steps));
        
        try {
            $tp1Steps = $route->getStepsByTestPart('TPX');
            $this->assertFalse(true);
        }
        catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
        
        // What are the Step objects involved in section 'S1'?
        $s1Steps = $route->getStepsByAssessmentSection($section1);
        $this->assertEquals(4, count($s1Steps));
        $this->assertEquals('Q1.0', $s1Steps[0]->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q2.0', $s1Steps[1]->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q3.0', $s1Steps[2]->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q4.0', $s1Steps[3]->getAssessmentItemOccurence()->getIdentifier());
        
        // What are the Step objects involved in section 'S2'?
        $s2Steps = $route->getStepsByAssessmentSection('S2');
        $this->assertEquals(2, count($s2Steps));
        $this->assertEquals('Q5.0', $s2Steps[0]->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q6.0', $s2Steps[1]->getAssessmentItemOccurence()->getIdentifier());
        
        // What are the Step objects involded in an unknown section :-D ?
        // An OutOfBoundsException must be thrown.
        try {
            $sXRouteItems = $route->getStepsByAssessmentSection(new AssessmentSection('SX', 'Unknown Section', true));
            $this->assertTrue(false, 'An exception must be thrown because the AssessmentSection object is not known by the Route.');
        }
        catch (OutOfBoundsException $e) {
            $this->assertTrue(true);
        }
        
        // Only 1 one occurence of each selected item found?
        foreach (array($q1, $q2, $q3, $q4, $q5, $q6) as $itemOccurence) {
            $this->assertEquals(1, $route->getOccurenceCount($itemOccurence->getAssessmentItemRef()));
        }
        
        $assessmentItemRefs = $route->getAssessmentItemRefs();
        $this->assertEquals(6, count($assessmentItemRefs));
        
        // test to retrieve items by category.
        $mathRefs = $route->getAssessmentItemRefsByCategory('mathematics');
        $this->assertEquals(3, count($mathRefs));
        
        $sciencesRefs = $route->getAssessmentItemRefsByCategory('sciences');
        $this->assertEquals(1, count($sciencesRefs));
        
        $mathAndSciences = $route->getAssessmentItemRefsByCategory(new IdentifierCollection(array('mathematics', 'sciences')));
        $this->assertEquals(4, count($mathAndSciences));
        
        $expertRefs = $route->getAssessmentItemRefsByCategory('expert');
        $this->assertEquals(2, count($expertRefs));
        
        // test to retrieve items by section.
        $section1Refs = $route->getAssessmentItemRefsBySection('S1');
        $this->assertEquals(4, count($section1Refs));
        
        $section2Refs = $route->getAssessmentItemRefsBySection('S2');
        $this->assertEquals(2, count($section2Refs));
        
        // test to retrieve items by section/category.
        $section1Refs = $route->getAssessmentItemRefsSubset('S1');
        $this->assertEquals(4, count($section1Refs));
        
        $mathRefs = $route->getAssessmentItemRefsSubset('', new IdentifierCollection(array('mathematics')));
        $this->assertEquals(3, count($mathRefs));
        
        $s1MathRefs = $route->getAssessmentItemRefsSubset('S1', new IdentifierCollection(array('mathematics')));
        $this->assertEquals(2, count($s1MathRefs));
        
        // go by exclusion.
        $exclusionRefs = $route->getAssessmentItemRefsSubset('', null, new IdentifierCollection(array('sciences', 'expert')));
        $this->assertEquals(4, count($exclusionRefs));
        $this->assertEquals('Q3', $exclusionRefs['Q3']->getIdentifier());
        $this->assertEquals('Q4', $exclusionRefs['Q4']->getIdentifier());
        $this->assertEquals('Q5', $exclusionRefs['Q5']->getIdentifier());
        $this->assertEquals('Q6', $exclusionRefs['Q6']->getIdentifier());
    }
    
    public function testOccurences() {
        $assessmentTest = new AssessmentTest('test', 'A Test');
        $testPart = new TestPart('T1');
        $section1 = new AssessmentSection('S1', 'Section1', true);
        $itemRef1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $item1 = new AssessmentItemOccurence($itemRef1);
        $itemRef2 = new AssessmentItemRef('Q2', 'Q2.xml');
        $item2 = new AssessmentItemOccurence($itemRef2);
        $itemRef3 = new AssessmentItemRef('Q3', 'Q3.xml');
        $item3 = new AssessmentItemOccurence($itemRef3);
        $sections = new AssessmentSectionCollection(array($section1));
        
        $route = new Route();
        $route->addStep(new Step($assessmentTest, $testPart, $sections, $item1));
        $route->addStep(new Step($assessmentTest, $testPart, $sections, $item2));
        $route->addStep(new Step($assessmentTest, $testPart, $sections, $item3));
        
        $this->assertEquals(0, $route->getStepAt(0)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(1)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(2)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(1, $route->getOccurenceCount($itemRef1));
        $this->assertEquals(1, $route->getOccurenceCount($itemRef2));
        $this->assertEquals(1, $route->getOccurenceCount($itemRef3));
        
        $item4 = new AssessmentItemOccurence($itemRef3);
        $route->addStep(new Step($assessmentTest, $testPart, $sections, $item4));
        $this->assertEquals(2, $route->getOccurenceCount($itemRef3));
        $this->assertEquals(1, $route->getStepAt(3)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(2)->getAssessmentItemOccurence()->getOccurence());
        
        
    }
    
    public function testGetStepAt() {
        $assessmentTest = new AssessmentTest('test', 'A Test');
        $testPart = new TestPart('T1');
        $section1 = new AssessmentSection('S1', 'Section1', true);
        $itemRef1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $item1 = new AssessmentItemOccurence($itemRef1);
        $itemRef2 = new AssessmentItemRef('Q2', 'Q2.xml');
        $item2 = new AssessmentItemOccurence($itemRef2);
        $itemRef3 = new AssessmentItemRef('Q3', 'Q3.xml');
        $item3 = new AssessmentItemOccurence($itemRef3);
        $sections = new AssessmentSectionCollection(array($section1));
        
        $steps = new StepCollection();
        $steps[] = new Step($assessmentTest, $testPart, $sections, $item1);
        $steps[] = new Step($assessmentTest, $testPart, $sections, $item2);
        $steps[] = new Step($assessmentTest, $testPart, $sections, $item3);
        
        $route = new Route($steps);
        $this->assertSame($steps[0], $route->getStepAt(0));
        $this->assertSame($steps[1], $route->getStepAt(1));
        $this->assertSame($steps[2], $route->getStepAt(2));
    }
    
    /**
     * @depends testGetStepAt
     */
    public function testPositionning() {
        // This test aims at testing if everything is fine with the configuration
        // of steps when added to the route.
        $assessmentTest = new AssessmentTest('test', 'A Test');
        $testPart = new TestPart('T1');
        $section1 = new AssessmentSection('S1', 'Section1', true);
        $itemRef1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $item1 = new AssessmentItemOccurence($itemRef1);
        $itemRef2 = new AssessmentItemRef('Q2', 'Q2.xml');
        $item2 = new AssessmentItemOccurence($itemRef2);
        $itemRef3 = new AssessmentItemRef('Q3', 'Q3.xml');
        $item3 = new AssessmentItemOccurence($itemRef3);
        $sections = new AssessmentSectionCollection(array($section1));
        
        $steps = new StepCollection();
        $steps[] = new Step($assessmentTest, $testPart, $sections, $item1);
        $steps[] = new Step($assessmentTest, $testPart, $sections, $item2);
        $steps[] = new Step($assessmentTest, $testPart, $sections, $item3);
        
        $route = new Route($steps);
        $this->assertEquals(0, $route->getStepAt(0)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(1)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(2)->getAssessmentItemOccurence()->getOccurence());
        
        $item4 = new AssessmentItemOccurence($itemRef1);
        $route->addStep(new Step($assessmentTest, $testPart, $sections, $item4));
        // Impact on positioned steps?
        $this->assertEquals(0, $route->getStepAt(0)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(1)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(2)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(1, $route->getStepAt(3)->getAssessmentItemOccurence()->getOccurence());
        
        // If I manually set up a fancy occurence number...
        // Will the route correctly reorganize that?
        $item5 = new AssessmentItemOccurence($itemRef1, 223);
        $route->addStep(new Step($assessmentTest, $testPart, $sections, $item5));
        $this->assertEquals(0, $route->getStepAt(0)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(1)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(0, $route->getStepAt(2)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(1, $route->getStepAt(3)->getAssessmentItemOccurence()->getOccurence());
        $this->assertEquals(2, $route->getStepAt(4)->getAssessmentItemOccurence()->getOccurence());
    }
    
    public function testIsX() {
        $route = self::buildSimpleRoute();
        
        // Q1
        $this->assertTrue($route->isNavigationLinear());
        $this->assertFalse($route->isNavigationNonLinear());
        $this->assertTrue($route->isSubmissionIndividual());
        $this->assertFalse($route->isSubmissionSimultaneous());
        $this->assertTrue($route->isFirst());
        $this->assertFalse($route->isLast());
        $route->next();
        
        // Q2
        $this->assertTrue($route->isNavigationLinear());
        $this->assertFalse($route->isNavigationNonLinear());
        $this->assertTrue($route->isSubmissionIndividual());
        $this->assertFalse($route->isSubmissionSimultaneous());
        $this->assertFalse($route->isFirst());
        $this->assertFalse($route->isLast());
        $route->next();
        
        // Q3
        $this->assertTrue($route->isNavigationLinear());
        $this->assertFalse($route->isNavigationNonLinear());
        $this->assertTrue($route->isSubmissionIndividual());
        $this->assertFalse($route->isSubmissionSimultaneous());
        $this->assertFalse($route->isFirst());
        $this->assertTrue($route->isLast());
        
        $route->next();
        $this->assertFalse($route->valid());
    }
    
    public function testPreviousNext() {
        $route = self::buildSimpleRoute();
        $this->assertEquals(0, $route->getPosition());
        
        // We are at first position, nothing should happen.
        // Q1
        $route->previous();
        $this->assertEquals(0, $route->getPosition());
        $this->assertEquals('Q1.0', $route->current()->getAssessmentItemOccurence()->getIdentifier());
        
        // go to Q2
        $route->next();
        $this->assertEquals(1, $route->getPosition());
        $this->assertEquals('Q2.0', $route->current()->getAssessmentItemOccurence()->getIdentifier());
        
        // go to Q3
        $route->next();
        $this->assertEquals(2, $route->getPosition());
        $this->assertEquals('Q3.0', $route->current()->getAssessmentItemOccurence()->getIdentifier());
        
        // go back to Q2
        $route->previous();
        $this->assertEquals(1, $route->getPosition());
        $this->assertEquals('Q2.0', $route->current()->getAssessmentItemOccurence()->getIdentifier());
        
        // go to Q3
        $route->next();
        $this->assertEquals('Q3.0', $route->current()->getAssessmentItemOccurence()->getIdentifier());
        
        // go beyond the digital nirvana, end of test.
        $route->next();
        $this->assertFalse($route->valid());
    }
    
    public function testGetNext() {
        $route = self::buildSimpleRoute();
        
        // Q1 - First position.
        $nextItem = $route->getNext();
        $this->assertEquals('Q2.0', $nextItem->getAssessmentItemOccurence()->getIdentifier());
        $route->next();
        
        // Q2 - Second position.
        $nextItem = $route->getNext();
        $this->assertEquals('Q3.0', $nextItem->getAssessmentItemOccurence()->getIdentifier());
        $route->next();
        
        // Q3 - Thrid position, there is no next route item.
        $this->setExpectedException('\\OutOfBoundsException');
        $nextItem = $route->getNext();
    }
    
    public function testGetPrevious() {
        $route = self::buildSimpleRoute();
        $route->next();
        
        // Q2 - Second postion.
        $previousItem = $route->getPrevious();
        $this->assertEquals('Q1.0', $previousItem->getAssessmentItemOccurence()->getIdentifier());
        $route->next();
        
        // Q3 - Third position.
        $previousItem = $route->getPrevious();
        $this->assertEquals('Q2.0', $previousItem->getAssessmentItemOccurence()->getIdentifier());
        
        // Go to Q1 to test exception.
        $route->previous();
        $route->previous();
        
        $this->assertEquals('Q1.0', $route->current()->getAssessmentItemOccurence()->getIdentifier());
        $this->setExpectedException('\\OutOfBoundsException');
        $route->getPrevious();
    }
    
    public function testGetCurrentTestPartSteps() {
        $route = self::buildSimpleRoute();
        $steps = $route->getCurrentTestPartSteps();
        $this->assertEquals(3, count($steps));
        $this->assertEquals('Q1.0', $steps[0]->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q2.0', $steps[1]->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q3.0', $steps[2]->getAssessmentItemOccurence()->getIdentifier());
    }
    
    public function testGetLastStep() {
        $route = self::buildSimpleRoute();
        $this->assertEquals('Q3.0', $route->getLastStep()->getAssessmentItemOccurence()->getIdentifier());
    }
    
    public function testGetLastStepEmptyRoute() {
        $route = new Route();
        $this->setExpectedException('\\OutOfBoundsException');
        $step = $route->getLastStep();
    }
    
    public function testGetFirstStep() {
        $route = self::buildSimpleRoute();
        $this->assertEquals('Q1.0', $route->getFirstStep()->getAssessmentItemOccurence()->getIdentifier());
    }
    
    public function testGetFirstStepEmptyRoute() {
        $route = new Route();
        $this->setExpectedException('\\OutOfBoundsException');
        $step = $route->getFirstStep();
    }
    
    public function testGetCategories() {
        $route = new Route();
        $test = new AssessmentTest('test1', 'Test 1');
        $testPart = new TestPart('T1');
        $section1 = new AssessmentSection('section1', 'Section 1');
        $sections = new AssessmentSectionCollection(array($section1));
        
        $itemRef1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $itemRef2 = new AssessmentItemRef('Q2', 'Q2.xml', new IdentifierCollection(array('mathematics')));
        $itemRef3 = new AssessmentItemRef('Q3', 'Q3.xml', new IdentifierCollection(array('literacy', 'mathematics')));
        
        $route = new Route();
        $route->addStep(new Step($test, $testPart, $sections, new AssessmentItemOccurence($itemRef1)));
        $this->assertEquals(array(), $route->getCategories()->getArrayCopy());
        
        $route->addStep(new Step($test, $testPart, $sections, new AssessmentItemOccurence($itemRef2)));
        $this->assertEquals(array('mathematics'), $route->getCategories()->getArrayCopy());
        
        $route->addStep(new Step($test, $testPart, $sections, new AssessmentItemOccurence($itemRef3)));
        $this->assertEquals(array('mathematics', 'literacy'), $route->getCategories()->getArrayCopy());
    }
    
    public function testAppendRoute() {
        $route = new Route();
        $test = new AssessmentTest('test1', 'Test 1');
        $testPart = new TestPart('T2');
        $section1 = new AssessmentSection('S2', 'Section 2');
        $sections = new AssessmentSectionCollection(array($section1));
        
        $route->addStep(new Step($test, $testPart, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q4', 'Q4.xml'))));
        $route->addStep(new Step($test, $testPart, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q5', 'Q5.xml'))));
        $route->addStep(new Step($test, $testPart, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q6', 'Q6.xml'))));
        
        $baseRoute = self::buildSimpleRoute();
        $baseRoute->appendRoute($route);
        
        $this->assertEquals(6, $baseRoute->count());
        $this->assertEquals('Q1.0', $baseRoute->getStepAt(0)->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q2.0', $baseRoute->getStepAt(1)->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q3.0', $baseRoute->getStepAt(2)->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q4.0', $baseRoute->getStepAt(3)->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q5.0', $baseRoute->getStepAt(4)->getAssessmentItemOccurence()->getIdentifier());
        $this->assertEquals('Q6.0', $baseRoute->getStepAt(5)->getAssessmentItemOccurence()->getIdentifier());
        
        $this->assertSame($test, $baseRoute->getStepAt(3)->getAssessmentTest());
        $this->assertSame($testPart, $baseRoute->getStepAt(3)->getTestPart());
        $this->assertSame($sections, $baseRoute->getStepAt(3)->getAssessmentSections());
        
        foreach ($baseRoute as $step) {
            $this->assertEquals(0, $step->getAssessmentItemOccurence()->getOccurence());
        }
    }
    
    public function testGetStepPosition() {
        $route = self::buildAverageRoute();
        for ($i = 0; $i < $route->count(); $i++) {
            $itemOccurence = $route->getStepAt($i)->getAssessmentItemOccurence();
            $this->assertEquals($itemOccurence->getIdentifier(), 'Q' . ($i + 1) . '.0');
        }
    }
    
    public function testSameReferences() {
        $test = new AssessmentTest('test1', 'Test 1');
        $part = new TestPart('T1');
        $section1 = new AssessmentSection('S1', 'Section 1');
        $sections = new AssessmentSectionCollection(array($section1));
        
        $route = new Route();
        $route->addStep(new Step($test, $part, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q1', 'Q1.xml'))));
        $route->addStep(new Step($test, $part, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q2', 'Q2.xml'))));
        $route->addStep(new Step($test, $part, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q3', 'Q3.xml'))));
        
        foreach ($route as $step) {
            $this->assertTrue($step->getAssessmentTest() === $test);
            $this->assertTrue($step->getTestPart() === $part);
            $this->assertTrue($step->getAssessmentSections() === $sections);
        }
    }
    
    /**
     * @dataProvider branchProvider
     * 
     * @param string $targetIdentifier
     * @param integer $expectedPosition
     */
    public function testBranch($targetIdentifier, $expectedPosition) {
        $route = self::buildAverageRoute();
        $route->branch($targetIdentifier);
        $this->assertEquals($expectedPosition, $route->getPosition(), "Invalid route position regarding the given branch target '${targetIdentifier}'.");
    }
    
    /**
     * @dataProvider branchOutsideOfCurrentTestPartProvider
     * 
     * @param string $targetIdentifier
     */
    public function testBranchOutsideOfCurrentTestPart($targetIdentifier) {
        $expectedMsg = "Branching to '${targetIdentifier}' failed: branch to items outside of the current testPart is forbidden by the QTI 2.1 specification.";
        $this->setExpectedException('\\OutOfBoundsException', $expectedMsg);
        $route = self::buildAverageRoute();
        $route->branch($targetIdentifier);
    }
    
    /**
     * @dataProvider branchSectionOutsideOfCurrentTestPartProvider
     *
     * @param string $targetIdentifier
     */
    public function testSectionBranchOutsideOfCurrentTestPart($targetIdentifier) {
        $expectedMsg = "Branching to '${targetIdentifier}' failed: branch to assessmentSections outside of the current testPart is forbidden by the QTI 2.1 specification.";
        $this->setExpectedException('\\OutOfBoundsException', $expectedMsg);
        $route = self::buildAverageRoute();
        $route->branch($targetIdentifier);
    }
    
    public function branchProvider() {
        return array(
            array('S1', 0),
            array('S1A', 0),
            array('S1B', 3),
            array('T2', 6),
            array('Q1', 0),
            array('Q2', 1),
            array('Q3', 2),
            array('Q4', 3),
            array('Q5', 4),
            array('Q6', 5)             
        );
    }
    
    public function branchOutsideOfCurrentTestPartProvider() {
        return array(
            array('Q7'),
            array('Q8'),
            array('Q9')
        );
    }
    
    public function branchSectionOutsideOfCurrentTestPartProvider() {
        return array(
            array('S2')                
        );
    }
    
    public function testBranchSameTestPartAsCurrent() {
        $route = self::buildAverageRoute();
        $expectedMsg = "Branching to 'T1' failed: branch to the same testPart as the current one is forbidden by the QTI 2.1 specification.";
        $this->setExpectedException('\\OutOfBoundsException', $expectedMsg);
        $route->branch('T1');
    }
    
    public function testBranchInvalidIdentifier() {
        $route = self::buildAverageRoute();
        $identifier = "Q1.-blu";
        $expectedMsg = "Branch failed: the given identifier '${identifier}' is an invalid branching target.";
        $this->setExpectedException('\\OutOfRangeException', $expectedMsg);
        $route->branch($identifier);
    }
    
    public function testCount() {
        $route = self::buildSimpleRoute();
        $this->assertEquals(3, $route->count());
        
        $route = self::buildAverageRoute();
        $this->assertEquals(9, $route->count());
    }
    
    public function testGetAllSteps() {
        $this->assertEquals(3, count(self::buildSimpleRoute()->getAllSteps()));
        $this->assertEquals(9, count(self::buildAverageRoute()->getAllSteps()));
    }
    
    public function testGetAssessmentItemRefs() {
        $itemRefs = self::buildAverageRoute()->getAssessmentItemRefs();
        for ($i = 0; $i < count($itemRefs); $i++) {
            $this->assertEquals('Q' . ($i + 1), $itemRefs['Q' . ($i + 1)]->getIdentifier());
        }
    }
    
    public function testGetAssessmentItemRefsByCategory() {
        $test = new AssessmentTest('test1', 'Test 1');
        $part = new TestPart('T1');
        $section1 = new AssessmentSection('S1', 'Section 1');
        $sections = new AssessmentSectionCollection(array($section1));
        
        $route = new Route();
        $this->assertEquals(0, count($route->getAssessmentItemRefsByCategory('category')));
        
        $route->addStep(new Step($test, $part, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q1', 'Q1.xml'))));
        $this->assertEquals(0, count($route->getAssessmentItemRefsByCategory('category')));
        
        $route->addStep(new Step($test, $part, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q2', 'Q2.xml', new IdentifierCollection(array('math'))))));
        $this->assertEquals(0, count($route->getAssessmentItemRefsByCategory('category')));
        $this->assertEquals(1, count($route->getAssessmentItemRefsByCategory('math')));
        
        $route->addStep(new Step($test, $part, $sections, new AssessmentItemOccurence(new AssessmentItemRef('Q3', 'Q3.xml', new IdentifierCollection(array('science', 'math'))))));
        $this->assertEquals(0, count($route->getAssessmentItemRefsByCategory('category')));
        $this->assertEquals(2, count($route->getAssessmentItemRefsByCategory('math')));
        $this->assertEquals(2, count($route->getAssessmentItemRefsByCategory(new IdentifierCollection(array('math', 'science')))));
    }
    
    public function testGetAssessmentItemRefsBySection() {
        $route = self::buildAverageRoute();
        $this->assertEquals(0, count($route->getAssessmentItemRefsBySection('XXX')));
        $this->assertEquals(6, count($route->getAssessmentItemRefsBySection('S1')));
        $this->assertEquals(3, count($route->getAssessmentItemRefsBySection('S1A')));
        $this->assertEquals(3, count($route->getAssessmentItemRefsBySection('S1B')));
        $this->assertEquals(3, count($route->getAssessmentItemRefsBySection('S2')));
    }
    
    /**
     * @dataProvider identifierSequenceProvider
     * 
     * @param Route $route
     * @param IdentifierCollection $expected
     * @param boolean $withOccurenceNumber
     */
    public function testIdentifierSequence(Route $route, IdentifierCollection $expected, $withOccurenceNumber) {
        $this->assertEquals($expected->getArrayCopy(), $route->getIdentifierSequence($withOccurenceNumber)->getArrayCopy());
    }
    
    public function identifierSequenceProvider() {
        return array(
            array(self::buildSimpleRoute(), new IdentifierCollection(array('Q1.1', 'Q2.1', 'Q3.1')), true),
            array(self::buildSimpleRoute(), new IdentifierCollection(array('Q1', 'Q2', 'Q3')), false),
            array(self::buildAverageRoute(), new IdentifierCollection(array('Q1.1', 'Q2.1', 'Q3.1', 'Q4.1', 'Q5.1', 'Q6.1', 'Q7.1', 'Q8.1', 'Q9.1')), true),
            array(self::buildAverageRoute(), new IdentifierCollection(array('Q1', 'Q2', 'Q3', 'Q4', 'Q5', 'Q6', 'Q7', 'Q8', 'Q9')), false),
        );
    }
    
    public function testIsInTestPart() {
        $route = self::buildAverageRoute();
        
        $t1 = $route->getStepAt(0)->getTestPart();
        
        $this->assertTrue($route->isInTestPart(0, $t1));
        $this->assertTrue($route->isInTestPart(1, $t1));
        $this->assertTrue($route->isInTestPart(2, $t1));
        $this->assertTrue($route->isInTestPart(3, $t1));
        $this->assertTrue($route->isInTestPart(4, $t1));
        $this->assertTrue($route->isInTestPart(5, $t1));
        $this->assertFalse($route->isInTestPart(6, $t1));
        $this->assertFalse($route->isInTestPart(7, $t1));
        $this->assertFalse($route->isInTestPart(8, $t1));
        
        $this->assertFalse($route->isInTestPart(0, new TestPart('T2')));
        
        $this->setExpectedException('\\OutOfBoundsException', "The position '19' is out of the bounds of the Route.");
        $route->isInTestPart(19, $t1);
    }
}