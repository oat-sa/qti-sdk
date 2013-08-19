<?php

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

use qtism\runtime\tests\Route;
use qtism\data\storage\xml\XmlAssessmentTestDocument;

class RouteTest extends QtiSmTestCase {
    
    public function testCreateFromAssessmentTest() {
        $doc = new XmlAssessmentTestDocument();
        $doc->load(self::samplesDir() . 'custom/selection_and_ordering.xml');
         
        $route = Route::createFromAssessmentTest($doc);
        
        $expectedRoute = array();
        $expectedRoute[] = array('Q1', 'S01A', 'testPart');
        $expectedRoute[] = array('Q2', 'S01A', 'testPart');
        $expectedRoute[] = array('Q3', 'S01A', 'testPart');
        $expectedRoute[] = array('Q4', 'S01B', 'testPart');
        $expectedRoute[] = array('Q5', 'S01B', 'testPart');
        $expectedRoute[] = array('Q6', 'S01B', 'testPart');
        $expectedRoute[] = array('Q7', 'S02', 'testPart');
        $expectedRoute[] = array('Q8', 'S02', 'testPart');
        $expectedRoute[] = array('Q9', 'S02', 'testPart');
        $expectedRoute[] = array('Q10', 'S03', 'testPart');
        $expectedRoute[] = array('Q11', 'S03', 'testPart');
        $expectedRoute[] = array('Q12', 'S03', 'testPart');
        
        $i = 0;
        while ($route->valid() === true) {
            $routeItem = $route->current();
            
            $this->assertEquals($expectedRoute[$i][0], $routeItem->getAssessmentItemRef()->getIdentifier());
            $this->assertEquals($expectedRoute[$i][1], $routeItem->getAssessmentSection()->getIdentifier());
            $this->assertEquals($expectedRoute[$i][2], $routeItem->getTestPart()->getIdentifier());
            
            $route->next();
            $i++;
        }
        
        $this->assertEquals(count($expectedRoute), $i);
    }
    
}