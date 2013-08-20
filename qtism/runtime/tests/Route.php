<?php

namespace qtism\runtime\tests;

use qtism\common\collections\IdentifierCollection;

use qtism\data\QtiComponentIterator;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use \Iterator;

/**
 * The Route class represents a linear route to be taken accross a given
 * selection of AssessmentItemRef objects.
 * 
 * A Route object is composed of RouteItem objects which are all composed
 * of three components:
 * 
 * * An AssessmentItemRef object.
 * * An AssessmentSection object, which is the parent section of the AssessmentItemRef.
 * * A TestPart object, which is the parent object (direct or indirect) of the AssessmentSection.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Route implements Iterator {
    
    private $routeItems = array();
    
    private $position = 0;
    
    /**
     * Create a new Route object.
     * 
     */
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
    
    /**
     * Add a new RouteItem object at the end of the Route.
     * 
     * @param RouteItem $routeItem A RouteItemObject.
     */
    public function addRouteItem(RouteItem $routeItem) {
        $routeItems = &$this->getRouteItems();
        array_push($routeItems, $routeItem);
    }
    
    public function rewind() {
        $this->setPosition(0);
    }
    
    /**
     * Get the current RouteItem object.
     * 
     * @return RouteItem A RouteItem object.
     */
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
    
    /**
     * Create a Route object from a given AssessmentTest object.
     * 
     * @param AssessmentTest $assessmentTest
     * @return Route A Route object.
     */
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
    
    /**
     * Get the sequence of identifiers formed by the identifiers of each
     * assessmentItemRef object of the route, in the order they must be taken.
     * 
     * @return IdentifierCollection
     */
    public function getIdentifierSequence() {
        $routeItems = &$this->getRouteItems();
        $collection = new IdentifierCollection();
        
        foreach (array_keys($routeItems) as $k) {
            $collection[] = $routeItems[$k]->getAssessmentItemRef()->getIdentifier();
        }
        
        return $collection;
    }
}