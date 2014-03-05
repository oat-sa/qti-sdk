<?php

use qtism\data\AssessmentItemRef;
use qtism\runtime\tests\routing\AssessmentItemOccurence;
use qtism\runtime\tests\routing\AssessmentItemOccurenceCollection;
use qtism\runtime\tests\routing\Step;
use qtism\runtime\tests\routing\Route;
use qtism\runtime\tests\routing\AssessmentSectionCollection;
use qtism\runtime\tests\routing\AssessmentSection;
use qtism\runtime\tests\routing\TestPart;
use qtism\runtime\tests\routing\AssessmentTest;

require_once(dirname(__FILE__) . '/../qtism/qtism.php');
require_once(dirname(__FILE__) . '/QtiSmTestCase.php');

abstract class QtiSmAltRouteTestCase extends QtiSmTestCase {
    
	public function setUp() {
	    parent::setUp();
	}
	
	public function tearDown() {
	    parent::tearDown();
	}
	
	/**
	 * Build a simple route:
	 *
	 * * Q1 - S1 - T1
	 * * Q2 - S1 - T1
	 * * Q3 - S1 - T1
	 *
	 * @param string $routeClass
	 * @return Route
	 */
	public static function buildSimpleRoute($routeClass = 'qtism\\runtime\\tests\\routing\\Route') {
	    $test = new AssessmentTest('test1', 'Test 1');
	    $testPart = new TestPart('T1');
	    $section1 = new AssessmentSection('S1', 'Section1', true);
	    $sections = new AssessmentSectionCollection(array($section1));
	    $items = new AssessmentItemOccurenceCollection();
	    $items[] = new AssessmentItemOccurence(new AssessmentItemRef('Q1', 'Q1.xml'));
	    $items[] = new AssessmentItemOccurence(new AssessmentItemRef('Q2', 'Q2.xml'));
	    $items[] = new AssessmentItemOccurence(new AssessmentItemRef('Q3', 'Q3.xml'));
	    
	    $route = new $routeClass();
	    $route->addStep(new Step($test, $testPart, $sections, $items[0]));
	    $route->addStep(new Step($test, $testPart, $sections, $items[1]));
	    $route->addStep(new Step($test, $testPart, $sections, $items[2]));
	
	    return $route;
	}
	
	/**
	 * Build an average route
	 * 
	 * * test 1 -> T1 -> S1 -> S1A -> Q1
	 * * test 1 -> T1 -> S1 -> S1A -> Q2
	 * * test 1 -> T1 -> S1 -> S1A -> Q3
	 * * test 1 -> T1 -> S1 -> S1B -> Q4
	 * * test 1 -> T1 -> S1 -> S1B -> Q5
	 * * test 1 -> T1 -> S1 -> S1B -> Q6
	 * * test 1 -> T2 -> S2 -> Q7
	 * * test 1 -> T2 -> S2 -> Q8
	 * * test 1 -> T2 -> S2 -> Q9
	 * 
	 * @param string $routeClass
	 * @return Route 
	 */
	public static function buildAverageRoute($routeClass = 'qtism\\runtime\\tests\\routing\\Route') {
	    $test = new AssessmentTest('test1', 'Test 1');
	    $testPart1 = new TestPart('T1');
	    $testPart2 = new TestPart('T2');
	    $section1 = new AssessmentSection('S1', 'Section 1');
	    $section1a = new AssessmentSection('S1A', 'Section 1A');
	    $section1b = new AssessmentSection('S1B', 'Section 1B');
	    $section2 = new AssessmentSection('S2', 'Section 2');
	    $sections1a = new AssessmentSectionCollection(array($section1, $section1a));
	    $sections1b = new AssessmentSectionCollection(array($section1, $section1b));
	    $sections2 = new AssessmentSectionCollection(array($section2));
	    $item1 = new AssessmentItemOccurence(new AssessmentItemRef('Q1', 'Q1.xml'));
	    $item2 = new AssessmentItemOccurence(new AssessmentItemRef('Q2', 'Q2.xml'));
	    $item3 = new AssessmentItemOccurence(new AssessmentItemRef('Q3', 'Q3.xml'));
	    $item4 = new AssessmentItemOccurence(new AssessmentItemRef('Q4', 'Q4.xml'));
	    $item5 = new AssessmentItemOccurence(new AssessmentItemRef('Q5', 'Q5.xml'));
	    $item6 = new AssessmentItemOccurence(new AssessmentItemRef('Q6', 'Q6.xml'));
	    $item7 = new AssessmentItemOccurence(new AssessmentItemRef('Q7', 'Q7.xml'));
	    $item8 = new AssessmentItemOccurence(new AssessmentItemRef('Q8', 'Q8.xml'));
	    $item9 = new AssessmentItemOccurence(new AssessmentItemRef('Q9', 'Q9.xml'));
	    
	    $route = new $routeClass();
	    $route->addStep(new Step($test, $testPart1, $sections1a, $item1));
	    $route->addStep(new Step($test, $testPart1, $sections1a, $item2));
	    $route->addStep(new Step($test, $testPart1, $sections1a, $item3));
	    $route->addStep(new Step($test, $testPart1, $sections1b, $item4));
	    $route->addStep(new Step($test, $testPart1, $sections1b, $item5));
	    $route->addStep(new Step($test, $testPart1, $sections1b, $item6));
	    $route->addStep(new Step($test, $testPart2, $sections2, $item7));
	    $route->addStep(new Step($test, $testPart2, $sections2, $item8));
	    $route->addStep(new Step($test, $testPart2, $sections2, $item9));
	    
	    return $route;
	}
}