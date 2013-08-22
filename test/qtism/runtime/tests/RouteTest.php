<?php
require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\common\collections\IdentifierCollection;
use qtism\runtime\tests\RouteItem;
use qtism\data\SectionPartCollection;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItem;
use qtism\data\AssessmentSectionCollection;
use qtism\data\TestPart;
use qtism\runtime\tests\Route;

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
        $route->addRouteItem(new RouteItem($sectionPartsS1['Q1'], $assessmentSections['S1'], $testPart));
        $route->addRouteItem(new RouteItem($sectionPartsS1['Q2'], $assessmentSections['S1'], $testPart));
        $route->addRouteItem(new RouteItem($sectionPartsS1['Q3'], $assessmentSections['S1'], $testPart));
        $route->addRouteItem(new RouteItem($sectionPartsS1['Q4'], $assessmentSections['S1'], $testPart));
        $route->addRouteItem(new RouteItem($sectionPartsS2['Q5'], $assessmentSections['S2'], $testPart));
        $route->addRouteItem(new RouteItem($sectionPartsS2['Q6'], $assessmentSections['S2'], $testPart));
        
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
        
        $mathRefs = $route->getAssessmentItemRefsSubset('', 'mathematics');
        $this->assertEquals(3, count($mathRefs));
        
        $s1MathRefs = $route->getAssessmentItemRefsSubset('S1', 'mathematics');
        $this->assertEquals(2, count($s1MathRefs));
    }
    
}