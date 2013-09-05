<?php

namespace qtism\runtime\tests;

use qtism\data\SubmissionMode;
use qtism\data\NavigationMode;
use qtism\data\AssessmentItemRefCollection;
use qtism\common\collections\IdentifierCollection;
use qtism\data\QtiComponentIterator;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use \Iterator;
use \SplObjectStorage;
use \OutOfBoundsException;

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
    
    /**
     * A map where each item is bound to a number of occurences.
     * 
     * @var SplObjectStorage
     */
    private $assessmentItemRefOccurenceCount;
    
    /**
     * The RouteItem objects the Route is composed with.
     * 
     * @var array
     */
    private $routeItems = array();
    
    /**
     * The current position in the route.
     * 
     * @var integer
     */
    private $position = 0;
    
    /**
     * A collection of identifier representing all the item categories
     * involved in the route.
     * 
     * @var IdentifierCollection
     */
    private $categories;
    
    /**
     * Create a new Route object.
     * 
     */
    public function __construct() {
        $this->setPosition(0);
        $this->setAssessmentItemRefs(new AssessmentItemRefCollection());
        $this->setAssessmentItemRefCategoryMap(array());
        $this->setAssessmentItemRefSectionMap(array());
        $this->setAssessmentItemRefOccurenceMap(new SplObjectStorage());
        $this->setCategories(new IdentifierCollection());
    }
    
    public function getPosition() {
        return $this->position;
    }
    
    public function setPosition($position) {
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
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * with a number of occurence.
     * 
     * @return SplObjectStorage
     */
    protected function getAssessmentItemRefOccurenceMap() {
        return $this->assessmentItemRefOccurenceCount;
    }
    
    /**
     * Set the map where AssessmentItemRef objects involved in the route are stored
     * with a number of occurence.
     * 
     * @param SplObjectStorage $assessmentItemRefOccurenceCount
     */
    protected function setAssessmentItemRefOccurenceMap(SplObjectStorage $assessmentItemRefOccurenceCount) {
        $this->assessmentItemRefOccurenceCount = $assessmentItemRefOccurenceCount;
    }
    
    /**
     * Set the collection of item categories involved in the route.
     * 
     * @param IdentifierCollection $categories A collection of QTI Identifiers.
     */
    protected function setCategories(IdentifierCollection $categories) {
        $this->categories = $categories;
    }
    
    /**
     * Get the collection of item categories involved in the route.
     * 
     * @return IdentifierCollection A collection of QTI Identifiers.
     */
    public function getCategories() {
        return $this->categories;
    }
    
    /**
     * Add a new RouteItem object at the end of the Route.
     * 
     * @param RouteItem $routeItem A RouteItemObject.
     */
    public function addRouteItem(AssessmentItemRef $assessmentItemRef, AssessmentSection $assessmentSection = null, TestPart $testPart) {
        // Push the routeItem in the track :) !
        $routeItem = new RouteItem($assessmentItemRef, $assessmentSection, $testPart);
        $this->registerAssessmentItemRef($routeItem);
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
    
    /**
     * Get the current key corresponding to the current RouteItem object.
     * 
     * @return integer The returned key is the position of the current RouteItem object in the Route.
     */
    public function key() {
        return $this->getPosition();
    }
    
    /**
     * Set the Route as its previous position in the RouteItem sequence. If the current
     * RouteItem is the first one prior to call next(), the Route remains in the same position.
     */
    public function previous() {
        $position = $this->getPosition();
        if ($position > 0) {
            $this->setPosition(--$position);
        }
    }
    
    /**
     * Set the Route as its next position in the RouteItem sequence. If the current
     * RouteItem is the last one prior to call next(), the iterator becomes invalid.
     */
    public function next() {
        $this->setPosition($this->getPosition() + 1);
    }
    
    /**
     * Whether the Route is still valid while iterating.
     * 
     * @return boolean
     */
    public function valid() {
        $routeItems = &$this->getRouteItems();
        return isset($routeItems[$this->getPosition()]);
    }
    
    /**
     * Whether the current RouteItem is the last of the route.
     * 
     * @return boolean
     */
    public function isLast() {
        $nextPosition = $this->getPosition() + 1;
        $routeItems = &$this->getRouteItems();
        return !isset($routeItems[$nextPosition]);
    }
    
    /**
     * Whether the current RouteItem is the first of the route.
     * 
     * @return boolean
     */
    public function isFirst() {
        return $this->getPosition() === 0;
    }
    
    /**
     * Whether the current RouteItem in the route is in linear
     * navigation mode.
     * 
     * @return boolean
     */
    public function isNavigationLinear() {
        return $this->current()->getTestPart()->getNavigationMode() === NavigationMode::LINEAR;
    }
    
    /**
     * Whether the current RouteItem in the route is in non-linear
     * navigation mode.
     * 
     * @return boolean
     */
    public function isNavigationNonLinear() {
        return !$this->isNavigationLinear();
    }
    
    /**
     * Whether the current RouteItem in the route is in individual
     * submission mode.
     * 
     * @return boolean
     */
    public function isSubmissionIndividual() {
        return $this->current()->getTestPart()->getSubmissionMode() === SubmissionMode::INDIVIDUAL;
    }
    
    /**
     * Whether the current RouteItem in the route is in simultaneous
     * submission mode.
     * 
     * @return boolean
     */
    public function isSubmissionSimultaneous() {
        return !$this->isSubmissionIndividual();
    }
    
    /**
     * Append all the RouteItem objects contained in $route
     * to this Route.
     * 
     * @param Route $route A Route object.
     */
    public function appendRoute(Route $route) {
        $routeItems = &$this->getRouteItems();
        
        foreach ($route as $routeItem) {
            $this->registerAssessmentItemRef(clone $routeItem);
        }    
    }
    
    /**
     * For more convience, the processing related to the AssessmentItemRef object contained
     * in a newly added RouteItem object is gathered in this method. The following process
     * will occur:
     * 
     * * The RouteItem object is inserted in the RouteItem array for storage.
     * * The assessmentItemRef is added to the occurence map.
     * * The assessmentItemRef is added to the category map.
     * * The assessmentItemRef is added to the section map.
     * 
     * @param RouteItem $routeItem
     */
    protected function registerAssessmentItemRef(RouteItem $routeItem) {
        $routeItems = &$this->getRouteItems();
        array_push($routeItems, $routeItem);
        
        // For more convenience ;)
        $assessmentItemRef = $routeItem->getAssessmentItemRef();
        
        // Count the number of occurences for the assessmentItemRef.
        $occurenceMap = $this->getAssessmentItemRefOccurenceMap();
        if (isset($occurenceMap[$assessmentItemRef]) === false) {
            $occurenceMap[$assessmentItemRef] = 0;
        }
        
        $occurenceMap[$assessmentItemRef] += 1;
        $routeItem->setOccurence($occurenceMap[$assessmentItemRef] - 1);
        
        // Reference the assessmentItemRef object of the RouteItem
        // for a later use.
        $this->getAssessmentItemRefs()->attach($assessmentItemRef);
        
        // Reference the assessmentItemRef object of the RouteItem
        // by category for a later use.
        $categoryMap = $this->getAssessmentItemRefCategoryMap();
        foreach ($assessmentItemRef->getCategories() as $category) {
            if (isset($categoryMap[$category]) === false) {
                $categoryMap[$category] = new AssessmentItemRefCollection();
            }
            $categoryMap[$category][] = $assessmentItemRef;
            
            $categories = $this->getCategories();
            if ($categories->contains($category) === false) {
                $categories[] = $category;
            }
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
    
    /**
     * Get the sequence of identifiers formed by the identifiers of each
     * assessmentItemRef object of the route, in the order they must be taken.
     * 
     * @param boolean $withSequenceNumber Whether to return the sequence number in the identifier or not.
     * @return IdentifierCollection
     */
    public function getIdentifierSequence($withSequenceNumber = true) {
        $routeItems = &$this->getRouteItems();
        $collection = new IdentifierCollection();
        
        foreach (array_keys($routeItems) as $k) {
            $virginIdentifier = $routeItems[$k]->getAssessmentItemRef()->getIdentifier();
            $collection[] = ($withSequenceNumber === true) ? $virginIdentifier . '.' . ($routeItems[$k]->getOccurence() + 1) : $virginIdentifier;
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
                foreach ($categoryMap[$cat] as $item) {
                    $result[] = $item;
                }
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
     * and categories to be included/excluded.
     * 
     * @param string $sectionIdentifier The identifier of the section.
     * @param IdentifierCollection $includeCategories A collection of category identifiers to be included in the selection.
     * @param IdentifierCollection $excludeCategories A collection of category identifiers to be excluded from the selection.
     * @return AssessmentItemRefCollection A collection of filtered AssessmentItemRef objects.
     * 
     */
    public function getAssessmentItemRefsSubset($sectionIdentifier = '', IdentifierCollection $includeCategories = null, IdentifierCollection $excludeCategories = null) {
        $bySection = (empty($sectionIdentifier) === true) ? $this->getAssessmentItemRefs() : $this->getAssessmentItemRefsBySection($sectionIdentifier);
        
        if (is_null($includeCategories) === false) {
            // We will perform the search by category inclusion.
            return $bySection->intersect($this->getAssessmentItemRefsByCategory($includeCategories));
        }
        else if (is_null($excludeCategories) === false) {
            // Perform the category by exclusion.
            return $bySection->diff($this->getAssessmentItemRefsByCategory($excludeCategories));
        }
        else {
            return $bySection;
        }
    }
    
    /**
     * Get the number of occurences found in the route for the given $assessmentItemRef.
     * If $assessmentItemRef is not involved in the route, the returned result is 0.
     * 
     * @param AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @return integer The number of occurences found in the route for $assessmentItemRef.
     */
    public function getOccurenceCount(AssessmentItemRef $assessmentItemRef) {
        
        $occurenceMap = $this->getAssessmentItemRefOccurenceMap();
        if (isset($occurenceMap[$assessmentItemRef]) === true) {
            return $occurenceMap[$assessmentItemRef];
        }
        else {
            return 0;
        }
    }
    
    /**
     * Get the number of RoutItem objects held by the Route.
     * 
     * @return integer
     */
    public function count() {
        return count($this->getRouteItems());
    }
    
    /**
     * Get a RouteItem object at $position in the route sequence. Please be careful that the route sequence index
     * begins at 0. In other words, the first route item in the sequence will be found at position 0, the second
     * at position 1, ...
     * 
     * @param integer $position The position of the requested RouteItem object in the route sequence.
     * @return RouteItem The RouteItem found at $position.
     * @throws OutOfBoundsException If no RouteItem is found at $position.
     */
    public function getRouteItemAt($position) {
        $routeItems = &$this->getRouteItems();
        
        if (isset($routeItems[$position]) === true) {
            return $routeItems[$position];
        }
        else {
            $msg = "No RouteItem object found at position '${position}'.";
            throw new OutOfBoundsException($msg);
        }
    }
    
    /**
     * Get the last RouteItem object composing the Route.
     * 
     * @return RouteItem The last RouteItem of the Route.
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function getLastRouteItem() {
        $routeItems = &$this->getRouteItems();
        $routeItemsCount = count($routeItems);
        
        if ($routeItemsCount === 0) {
            $msg = "Cannot get the last RouteItem of the Route while it is empty.";
            throw new OutOfBoundsException($msg);
        }
        
        return $routeItems[$routeItemsCount - 1];
    }
    
    /**
     * Get the first RouteItem object composing the Route.
     * 
     * @throws OutOfBoundsException If the Route is empty.
     * @return RouteItem The first RouteItem of the Route.
     */
    public function getFirstRouteItem() {
        $routeItems = &$this->getRouteItems();
        $routeItemsCount = count($routeItems);
        
        if ($routeItemsCount === 0) {
            $msg = "Cannot get the first RouteItem of the Route while it is empty.";
            throw new OutOfBoundsException($msg);
        }
        
        return $routeItems[0];
    }
}