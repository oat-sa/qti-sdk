<?php

namespace qtism\runtime\tests;

use qtism\data\QtiComponentIterator;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use \Iterator;

/**
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Route implements Iterator {
    
    private $routeItems = array();
    
    private $position = 0;
    
    public function __construct() {
        $this->setPosition(0);
    }
    
    protected function getPosition() {
        return $this->position;
    }
    
    protected function setPosition($position) {
        $this->position = $position;
    }
    
    protected function &getRouteItems() {
        return $this->routeItems;
    }
    
    public function addRouteItem(RouteItem $routeItem) {
        $routeItems = &$this->getRouteItems();
        array_push($routeItems, $routeItem);
    }
    
    public function rewind() {
        $this->setPosition(0);
    }
    
    public function current() {
        $routeItems = &$this->getRouteItems();
        return $routeItems[$this->getPosition()];
    }
    
    public function key() {
        return $this->getPosition();
    }
    
    public function next() {
        $this->setPosition($this->getPosition() + 1);
    }
    
    public function valid() {
        $routeItems = &$this->getRouteItems();
        return isset($routeItems[$this->getPosition()]);
    }
    
    public static function createFromAssessmentTest(AssessmentTest $assessmentTest) {
        $iterator = new QtiComponentIterator($assessmentTest);
        $route = new Route();
        
        while ($iterator->valid() === true) {
            
            $current = $iterator->current();
        
            if ($current instanceof TestPart) {
                $currentTestPart = $current;
            }
        
            if ($current instanceof AssessmentSection) {
                $currentSection = $current;
            }
        
            if ($current instanceof AssessmentItemRef) {
                $currentItem = $current;
                $routeItem = new RouteItem($currentItem, $currentSection, $currentTestPart);
                $route->addRouteItem($routeItem);  
            }
        
            $iterator->next();
        }
        
        return $route;
    }
}