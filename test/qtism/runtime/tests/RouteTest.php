<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactAssessmentTestDocument;
use qtism\common\collections\IdentifierCollection;
use qtism\runtime\tests\RouteItem;
use qtism\data\SectionPartCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItem;
use qtism\data\AssessmentSectionCollection;
use qtism\data\TestPart;
use qtism\runtime\tests\Route;
use qtism\data\TestPartCollection;
use qtism\data\AssessmentItemRefCollection;

class RouteTest extends QtiSmTestCase {
    
    public function testRouteTest() {
        
        $assessmentSections = new AssessmentSectionCollection();
        $assessmentSections[] = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSections[] = new AssessmentSection('S2', 'Section 2', true);
        
        $q1 = new AssessmentItemRef('Q1', 'Q1.xml');
        $q1->setCategories(new IdentifierCollection(array('mathematics', 'expert')));
        $q2 = new AssessmentItemRef('Q2', 'Q2.xml');
        $q2->setCategories(new IdentifierCollection(array('sciences', 'expert')));
        $q3 = new AssessmentItemRef('Q3', 'Q3.xml');
        $q3->setCategories(new IdentifierCollection(array('mathematics')));
        $q4 = new AssessmentItemRef('Q4', 'Q4.xml');
        $sectionPartsS1 = new SectionPartCollection(array($q1, $q2, $q3, $q4));
        $assessmentSections['S1']->setSectionParts($sectionPartsS1);
        
        $q5 = new AssessmentItemRef('Q5', 'Q5.xml');
        $q6 = new AssessmentItemRef('Q6', 'Q6.xml');
        $q6->setCategories(new IdentifierCollection(array('mathematics')));
        $sectionPartsS2 = new SectionPartCollection(array($q5, $q6));
        $assessmentSections['S2']->setSectionParts($sectionPartsS2);
        
        $testPart = new TestPart('TP1', $assessmentSections);
        $testPart->setAssessmentSections($assessmentSections);
        
        $route = new Route();
        $route->addRouteItem($sectionPartsS1['Q1'], $assessmentSections['S1'], $testPart);
        $route->addRouteItem($sectionPartsS1['Q2'], $assessmentSections['S1'], $testPart);
        $route->addRouteItem($sectionPartsS1['Q3'], $assessmentSections['S1'], $testPart);
        $route->addRouteItem($sectionPartsS1['Q4'], $assessmentSections['S1'], $testPart);
        $route->addRouteItem($sectionPartsS2['Q5'], $assessmentSections['S2'], $testPart);
        $route->addRouteItem($sectionPartsS2['Q6'], $assessmentSections['S2'], $testPart);
        
        // Only 1 one occurence of each selected item found?
        foreach (array_merge($sectionPartsS1->getArrayCopy(), $sectionPartsS2->getArrayCopy()) as $itemRef) {
            $this->assertEquals(1, $route->getOccurenceCount($itemRef));
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
        $this->assertTrue(isset($exclusionRefs['Q3']));
        $this->assertTrue(isset($exclusionRefs['Q4']));
        $this->assertTrue(isset($exclusionRefs['Q5']));
        $this->assertTrue(isset($exclusionRefs['Q6']));
    }
    
    public function testOccurences() {
        $assessmentItemRefs = new AssessmentItemRefCollection();
        $assessmentItemRefs[] = new AssessmentItemRef('Q1', 'Q1.xml');
        $assessmentItemRefs[] = new AssessmentItemRef('Q2', 'Q2.xml');
        $assessmentItemRefs[] = new AssessmentItemRef('Q3', 'Q3.xml');
        
        $assessmentSections = new AssessmentSectionCollection();
        $assessmentSections[] = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSections['S1']->setSectionParts($assessmentItemRefs);
        
        $testParts = new TestPartCollection();
        $testParts[] = new TestPart('T1', $assessmentSections);
        
        $route = new Route();
        
        $route->addRouteItem($assessmentItemRefs['Q1'], $assessmentSections['S1'], $testParts['T1']);
        $route->addRouteItem($assessmentItemRefs['Q2'], $assessmentSections['S1'], $testParts['T1']);
        $route->addRouteItem($assessmentItemRefs['Q3'], $assessmentSections['S1'], $testParts['T1']);
        
        $this->assertEquals(1, $route->getOccurenceCount($assessmentItemRefs['Q1']));
        $this->assertEquals(1, $route->getOccurenceCount($assessmentItemRefs['Q2']));
        $this->assertEquals(1, $route->getOccurenceCount($assessmentItemRefs['Q3']));
        
        $route->addRouteItem($assessmentItemRefs['Q3'], $assessmentSections['S1'], $testParts['T1']);
        $this->assertEquals(2, $route->getOccurenceCount($assessmentItemRefs['Q3']));
        
        // Get the second route item in the route.
        $routeItem2 = $route->getRouteItemAt(1);
        $this->assertEquals('Q2', $routeItem2->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $routeItem2->getOccurence());
        
        $routeItem3 = $route->getRouteItemAt(2);
        $this->assertEquals('Q3', $routeItem3->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(0, $routeItem3->getOccurence());
        
        $routeItem4 = $route->getRouteItemAt(3);
        $this->assertEquals('Q3', $routeItem4->getAssessmentItemRef()->getIdentifier());
        $this->assertEquals(1, $routeItem4->getOccurence());
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
        $this->assertEquals('Q1', $route->current()->getAssessmentItemRef()->getIdentifier());
        
        // go to Q2
        $route->next();
        $this->assertEquals(1, $route->getPosition());
        $this->assertEquals('Q2', $route->current()->getAssessmentItemRef()->getIdentifier());
        
        // go to Q3
        $route->next();
        $this->assertEquals(2, $route->getPosition());
        $this->assertEquals('Q3', $route->current()->getAssessmentItemRef()->getIdentifier());
        
        // go back to Q2
        $route->previous();
        $this->assertEquals(1, $route->getPosition());
        $this->assertEquals('Q2', $route->current()->getAssessmentItemRef()->getIdentifier());
        
        // go to Q3
        $route->next();
        $this->assertEquals('Q3', $route->current()->getAssessmentItemRef()->getIdentifier());
        
        // go beyond the digital nirvana, end of test.
        $route->next();
        $this->assertFalse($route->valid());
    }
    
    /**
     * Build a simple route:
     * 
     * * Q1 - S1 - T1
     * * Q2 - S1 - T1
     * * Q3 - S1 - T1
     * 
     * @return Route
     */
    protected static function buildSimpleRoute() {
        $assessmentItemRefs = new AssessmentItemRefCollection();
        $assessmentItemRefs[] = new AssessmentItemRef('Q1', 'Q1.xml');
        $assessmentItemRefs[] = new AssessmentItemRef('Q2', 'Q2.xml');
        $assessmentItemRefs[] = new AssessmentItemRef('Q3', 'Q3.xml');
        
        $assessmentSections = new AssessmentSectionCollection();
        $assessmentSections[] = new AssessmentSection('S1', 'Section 1', true);
        $assessmentSections['S1']->setSectionParts($assessmentItemRefs);
        
        $testParts = new TestPartCollection();
        $testParts[] = new TestPart('T1', $assessmentSections);
        
        $route = new Route();
        
        $route->addRouteItem($assessmentItemRefs['Q1'], $assessmentSections['S1'], $testParts['T1']);
        $route->addRouteItem($assessmentItemRefs['Q2'], $assessmentSections['S1'], $testParts['T1']);
        $route->addRouteItem($assessmentItemRefs['Q3'], $assessmentSections['S1'], $testParts['T1']);
        
        return $route;
    }
}