<?php

namespace qtism\runtime\tests;

use qtism\data\AssessmentItemRefCollection;
use qtism\common\collections\IdentifierCollection;
use qtism\data\QtiComponentIterator;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use \Iterator;
use \SplObjectStorage;

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
    
    /**
     * A collection that gathers all assessmentItemRefs
     * involved in the route.
     * 
     * @var AssessmentItemRefCollection
     */
    private $assessmentItemRefs;
    
    /**
     * A map where items are gathered by category.
     * 
     * @var array
     */
    private $assessmentItemRefCategoryMap;
    
    /**
     * A map where items are gathered by section identifier.
     * 
     * @var array
     */
    private $assessmentItemRefSectionMap;
    
    private $routeItems = array();
    
    private $position = 0;
    
    /**
     * Create a new Route object.
     * 
     */
    public function __construct() {
        $this->setPosition(0);
        $this->setAssessmentItemRefs(new AssessmentItemRefCollection());
        $this->setAssessmentItemRefCategoryMap(array());
        $this->setAssessmentItemRefSectionMap(array());
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
     * Get the collection of AssessmentItemRef objects
     * that are involded in the route.
     * 
     * @return AssessmentItemRefCollection A collection of AssessmentItemRef objects.
     */
    public function getAssessmentItemRefs() {
        return $this->assessmentItemRefs;
    }
    
    /**
     * Set the collection of AssessmentItemRef objects that are involved
     * in this route.
     * 
     * @param AssessmentItemRefCollection $assessmentItemRefs A collection of AssessmentItemRefObjects.
     */
    public function setAssessmentItemRefs(AssessmentItemRefCollection $assessmentItemRefs) {
        $this->assessmentItemRefs = $assessmentItemRefs;
    }
    
    /**
     * Get the map where AssessmentItemRef objects involved in the route are
     * stored by category.
     * 
     * @return array A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same category.
     */
    protected function getAssessmentItemRefCategoryMap() {
        return $this->assessmentItemRefCategoryMap;
    }

    /**
     * Set the map where AssessmentItemRef objects involved in the route are stored
     * by category.
     * 
     * @param array $assessmentItemRefCategoryMap A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef object of the same category.
     */
    protected function setAssessmentItemRefCategoryMap(array $assessmentItemRefCategoryMap) {
        $this->assessmentItemRefCategoryMap = $assessmentItemRefCategoryMap;
    }
    
    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * by section.
     * 
     * @return array A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same section.
     */
    protected function getAssessmentItemRefSectionMap() {
        return $this->assessmentItemRefSectionMap;
    }
    
    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * by section.
     * 
     * @param array $assessmentItemRefSectionMap A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same section.
     */
    protected function setAssessmentItemRefSectionMap(array $assessmentItemRefSectionMap) {
        $this->assessmentItemRefSectionMap = $assessmentItemRefSectionMap;
    }
    
    /**
     * Add a new RouteItem object at the end of the Route.
     * 
     * @param RouteItem $routeItem A RouteItemObject.
     */
    public function addRouteItem(RouteItem $routeItem) {
        // Push the routeItem in the track :) !
        $routeItems = &$this->getRouteItems();
        array_push($routeItems, $routeItem);
        
        // Reference the assessmentItemRef object of the RouteItem
        // for a later use.
        $assessmentItemRef = $routeItem->getAssessmentItemRef();
        $this->getAssessmentItemRefs()->attach($assessmentItemRef);
        
        // Reference the assessmentItemRef object of the RouteItem
        // by category for a later use.
        $categoryMap = $this->getAssessmentItemRefCategoryMap();
        foreach ($assessmentItemRef->getCategories() as $category) {
            if (isset($categoryMap[$category]) === false) {
                $categoryMap[$category] = new AssessmentItemRefCollection();
            }
            
            $categoryMap[$category][] = $assessmentItemRef;
        }
        
        $this->setAssessmentItemRefCategoryMap($categoryMap);
        
        // Reference the AssessmentItemRef object of the RouteItem
        // by section for a later use.
        $sectionMap = $this->getAssessmentItemRefSectionMap();
        $assessmentSectionIdentifier = $routeItem->getAssessmentSection()->getIdentifier();
        if (isset($sectionMap[$assessmentSectionIdentifier]) === false) {
            $sectionMap[$assessmentSectionIdentifier] = new AssessmentItemRefCollection();
        }
        $sectionMap[$assessmentSectionIdentifier][] = $assessmentItemRef;
        
        $this->setAssessmentItemRefSectionMap($sectionMap);
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
     * Append all the RouteItem objects contained in $route
     * to this Route.
     * 
     * @param Route $route A Route object.
     */
    public function appendRoute(Route $route) {
        foreach ($route as $routeItem) {
            $this->addRouteItem($routeItem);
        }    
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
    
    /**
     * Get the AssessmentItemRef objects involved in the route that belong
     * to a given $category.
     * 
     * If no AssessmentItemRef involved in the route are found for the given $category,
     * the return AssessmentItemRefCollection is empty.
     * 
     * @param string|IdentifierCollection $category A category identifier.
     * @return AssessmentItemRefCollection An collection of AssessmentItemRefCollection that belong to $category.
     */
    public function getAssessmentItemRefsByCategory($category) {
        
        $categoryMap = $this->getAssessmentItemRefCategoryMap();
        $categories = (is_string($category)) ? array($category) : $category->getArrayCopy();

        $result = new AssessmentItemRefCollection();
        
        foreach ($categories as $cat) {
            if (isset($categoryMap[$cat]) === true) {
                $result->merge($categoryMap[$cat]);
            }
        }

        return $result;
    }
    
    /**
     * Get a subset of AssessmentItemRef objects by $sectionIdentifier. If no items are matching $sectionIdentifier,
     * an empty collection is returned.
     * 
     * @param string $sectionIdentifier A section identifier.
     * @return AssessmentItemRefCollection A Collection of AssessmentItemRef objects that belong to the section $sectionIdentifier.
     */
    public function getAssessmentItemRefsBySection($sectionIdentifier) {
        
        $sectionMap = $this->getAssessmentItemRefSectionMap();
        
        if (isset($sectionMap[$sectionIdentifier])) {
            return $sectionMap[$sectionIdentifier];
        }
        else {
            return new AssessmentItemRefCollection();
        }
    }
    
    /**
     * Get a subset of AssessmentItemRef objects. The criterias are the $sectionIdentifier
     * 
     * @param string $sectionIdentifier The identifier of the section.
     * @param string|IdentifierCollection $category A string or a collection of identifiers to filter the categories of items.
     */
    public function getAssessmentItemRefsSubset($sectionIdentifier = '', $category = '') {
        
        if (empty($sectionIdentifier) && empty($category)) {
            return $this->getAssessmentItemRefs();
        }
        else if (empty($sectionIdentifier)) {
            // by category only.
            return $this->getAssessmentItemRefsByCategory($category);
        }
        else if (empty($category)) {
            // by section only.
            return $this->getAssessmentItemRefsBySection($sectionIdentifier);
        }
        else {
            // both.
            $bySection = $this->getAssessmentItemRefsBySection($sectionIdentifier);
            $byCategory = $this->getAssessmentItemRefsByCategory($category);
            
            // get the intersection.
            $bySection->intersect($byCategory);
            
            return $bySection;
        }
    }
}