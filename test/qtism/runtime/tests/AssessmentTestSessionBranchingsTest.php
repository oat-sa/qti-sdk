<?php

use qtism\runtime\tests\AssessmentTestSessionFactory;
use qtism\runtime\tests\AssessmentTestSession;
use qtism\data\storage\xml\XmlCompactDocument;

require_once (dirname(__FILE__) . '/../../../QtiSmTestCase.php');

class AssessmentTestSessionBranchingsTest extends QtiSmTestCase {
	
    public function testInstantiationSample1() {
        
        $doc = new XmlCompactDocument('1.0');
        $doc->load(self::samplesDir() . 'custom/runtime/branchings/branchings_single_section_linear.xml');
        
        $factory = new AssessmentTestSessionFactory($doc->getDocumentComponent());
        $testSession = AssessmentTestSession::instantiate($factory);
        
        $route = $testSession->getRoute();
        
        // $routeItemQ01 must have a single branchRule targeting Q03.
        $routeItemQ01 = $route->getRouteItemAt(0);
        $branchRules = $routeItemQ01->getBranchRules();
        $this->assertEquals(1, count($branchRules));
        $this->assertEquals('Q03', $branchRules[0]->getTarget());
        
        // $routeItemQ02 must have a single branchRule targeting Q04.
        $routeItemQ02 = $route->getRouteItemAt(1);
        $branchRules = $routeItemQ02->getBranchRules();
        $this->assertEquals(1, count($branchRules));
        $this->assertEquals('Q04', $branchRules[0]->getTarget());
        
        // $routeItemQ03 must have a single branchRule targeting EXIT_TEST
        $routeItemQ03 = $route->getRouteItemAt(2);
        $branchRules = $routeItemQ03->getBranchRules();
        $this->assertEquals(1, count($branchRules));
        $this->assertEquals('EXIT_TEST', $branchRules[0]->getTarget());
        
        // $routeItemQ04 is the end of the test and has no branchRules.
        $routeItemQ04 = $route->getRouteItemAt(3);
        $this->assertEquals(0, count($routeItemQ04->getBranchRules()));
    }
}