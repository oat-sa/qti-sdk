<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package qtism
 * @subpackage
 *
 */
namespace qtism\runtime\tests\routing;

use qtism\runtime\common\VariableIdentifier;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentItemRefCollection;
use qtism\data\SubmissionMode;
use qtism\data\NavigationMode;
use \Iterator;
use \SplObjectStorage;
use \OutOfRangeException;
use \OutOfBoundsException;
use qtism\common\collections\IdentifierCollection;

/**
 * A Route represents a linear sequence of Steps to be taken during
 * an AssessmentTestSession.
 * 
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class Route implements Iterator {
    
    /**
     * A collection that gathers all AssessmentItemRef objects
     * involved in the Route.
     *
     * @var AssessmentItemRefCollection
     */
    private $assessmentItemRefs;
    
    /**
     * A map where AssessmentItemRef objects involved in the Route
     *  are gathered by AssessmentItemRef->category.
     *
     * @var array
     */
    private $assessmentItemRefCategoryMap;
    
    /**
     * A map where AssessmentItemRef objects involved in the Route
     * are gathered by AssessmentSection->identifier.
     *
     * @var array
     */
    private $assessmentItemRefSectionMap;
    
    /**
     * A map where each AssessmentItemRef object involved in the Route
     * is bound to a number of occurences.
     *
     * @var SplObjectStorage
     */
    private $assessmentItemRefOccurenceCount;
    
    /**
     * A map where each Step is bound to its owner TestPart object.
     *
     * @var SplObjectStorage
     */
    private $testPartMap;
    
    /**
     * A map where each Step is bound to a TestPart identifier.
     *
     * @var array
     */
    private $testPartIdentifierMap;
    
    /**
     * A map where each Step is bound to an AssessmentSection.
     *
     * @var SplObjectStorage
     */
    private $assessmentSectionMap;
    
    /**
     * A map where each Step is bound to an assessment section identifier.
     *
     * @var array
     */
    private $assessmentSectionIdentifierMap;
    
    /**
     * A map where each Step is bound to an assessmentItemRef.
     *
     * @var SplObjectStorage
     */
    private $assessmentItemRefMap;
    
    /**
     * The Step objects the Route is composed with.
     *
     * @var array
     */
    private $steps = array();
    
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
     * @param StepCollection $steps A collection of Step objects to compose the route.
     */
    public function __construct(StepCollection $steps = null) {
        $this->setPosition(0);
        $this->setAssessmentItemRefs(new AssessmentItemRefCollection());
        $this->setAssessmentItemRefCategoryMap(array());
        $this->setAssessmentItemRefSectionMap(array());
        $this->setAssessmentItemRefOccurenceMap(new SplObjectStorage());
        $this->setCategories(new IdentifierCollection());
        $this->setTestPartMap(new SplObjectStorage());
        $this->setAssessmentSectionMap(new SplObjectStorage());
        $this->setTestPartIdentifierMap(array());
        $this->setAssessmentSectionIdentifierMap(array());
        $this->setAssessmentItemRefMap(new SplObjectStorage());
        
        if ($steps !== null) {
            foreach ($steps as $step) {
                $this->addStep($step);
            }
        }
    }
    
    public function getPosition() {
        return $this->position;
    }
    
    public function setPosition($position) {
        $this->position = $position;
    }
    
    protected function &getSteps() {
        return $this->steps;
    }
    
    /**
     * Get the collection of AssessmentItemRef objects
     * that are involded in the Route.
     *
     * @return AssessmentItemRefCollection A collection of AssessmentItemRef objects.
     */
    public function getAssessmentItemRefs() {
        return $this->assessmentItemRefs;
    }
    
    /**
     * Set the collection of AssessmentItemRef objects that are involved
     * in this Route.
     *
     * @param AssessmentItemRefCollection $assessmentItemRefs A collection of AssessmentItemRef objects.
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
     * Set the map where Step objects are gathered by TestPart.
     *
     * @param SplObjectStorage $testPartMap
     */
    protected function setTestPartMap(SplObjectStorage $testPartMap) {
        $this->testPartMap = $testPartMap;
    }
    
    /**
     * Get the map where Step objects are gathered by TestPart.
     *
     * @return SplObjectStorage
     */
    protected function getTestPartMap() {
        return $this->testPartMap;
    }
    
    /**
     * Set the map where Step objects are gathered by TestPart identifier.
     *
     * @param array $testPartIdentifierMap
     */
    protected function setTestPartIdentifierMap(array $testPartIdentifierMap) {
        $this->testPartIdentifierMap = $testPartIdentifierMap;
    }
    
    /**
     * Get the map where Step objects are gathered by TestPart identifier.
     *
     * @return array
     */
    protected function getTestPartIdentifierMap() {
        return $this->testPartIdentifierMap;
    }
    
    /**
     * Set the map where Step objects are gathered by AssessmentSection.
     *
     * @param SplObjectStorage $assessmentSectionMap
     */
    protected function setAssessmentSectionMap(SplObjectStorage $assessmentSectionMap) {
        $this->assessmentSectionMap = $assessmentSectionMap;
    }
    
    /**
     * Get the map where Step objects are gathered by AssessmentSection.
     *
     * @return SplObjectStorage
     */
    protected function getAssessmentSectionMap() {
        return $this->assessmentSectionMap;
    }
    
    /**
     * Set the map where Step objects are gathered by AssessmentSection identifier.
     *
     * @param array $assessmentSectionIdentifierMap
     */
    protected function setAssessmentSectionIdentifierMap(array $assessmentSectionIdentifierMap) {
        $this->assessmentSectionIdentifierMap = $assessmentSectionIdentifierMap;
    }
    
    /**
     * Get the map where Step objects are gathered by AssessmentSection identifier.
     *
     * @return array
     */
    protected function getAssessmentSectionIdentifierMap() {
        return $this->assessmentSectionIdentifierMap;
    }
    
    /**
     * Set the map where Step objects are gathered by AssessmentItemRef objects.
     *
     * @param SplObjectStorage $assessmentItemRefMap
     */
    protected function setAssessmentItemRefMap(SplObjectStorage $assessmentItemRefMap) {
        $this->assessmentItemRefMap = $assessmentItemRefMap;
    }
    
    /**
     * Get the map where Step objects are gathered by AssessmentItemRef objects.
     *
     * @return SplObjectStorage
     */
    protected function getAssessmentItemRefMap() {
        return $this->assessmentItemRefMap;
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
    
    public function addStep(Step $step) {
        $this->registerAssessmentItemRef($step);
        $this->registerTestPart($step);
        $this->registerAssessmentSection($step);
    }
    
    /**
     * Get the current Step object.
     *
     * @return Step A Step object.
     */
    public function current() {
        $steps = &$this->getSteps();
        return $steps[$this->getPosition()];
    }
    
    /**
     * Get the current key corresponding to the current Step object.
     *
     * @return integer The returned key is the position of the current Step object in the Route.
     */
    public function key() {
        return $this->getPosition();
    }
    
    /**
     * Set the Route as its previous position in the Step sequence. If the current
     * Step is the first one prior to call next(), the Route remains in the same position.
     *
     */
    public function previous() {
        $position = $this->getPosition();
        if ($position > 0) {
            $this->setPosition(--$position);
        }
    }
    
    /**
     * Set the Route as its next position in the Step sequence. If the current
     * Step is the last one prior to call next(), the iterator becomes invalid.
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
        $steps = &$this->getSteps();
        return isset($steps[$this->getPosition()]);
    }
    
    public function rewind() {
        $this->setPosition(0);
    }
    
    /**
     * Whether the current Step is the last of the route.
     *
     * @return boolean
     */
    public function isLast() {
        $nextPosition = $this->getPosition() + 1;
        $steps = &$this->getSteps();
        return !isset($steps[$nextPosition]);
    }
    
    /**
     * Whether the current Step is the first of the route.
     *
     * @return boolean
     */
    public function isFirst() {
        return $this->getPosition() === 0;
    }
    
    /**
     * Whether the current Step in the route is in linear
     * navigation mode.
     *
     * @return boolean
     */
    public function isNavigationLinear() {
        return $this->current()->getNavigationMode() === NavigationMode::LINEAR;
    }
    
    /**
     * Whether the current Step in the route is in non-linear
     * navigation mode.
     *
     * @return boolean
     */
    public function isNavigationNonLinear() {
        return !$this->isNavigationLinear();
    }
    
    /**
     * Whether the current Step in the route is in individual
     * submission mode.
     *
     * @return boolean
     */
    public function isSubmissionIndividual() {
        return $this->current()->getSubmissionMode() === SubmissionMode::INDIVIDUAL;
    }
    
    /**
     * Whether the current Step in the route is in simultaneous
     * submission mode.
     *
     * @return boolean
     */
    public function isSubmissionSimultaneous() {
        return !$this->isSubmissionIndividual();
    }
    
    /**
     * Append all the Step objects contained in $route
     * to this Route.
     *
     * @param Route $route A Route object.
     */
    public function appendRoute(Route $route) {
    
        foreach ($route as $step) {
    
            // Clone the step in order to not change
            // the occurence number of the original.
            $this->addStep(clone $step);
        }
    }
    
    /**
     * For more convience, the processing related to the AssessmentItemRef object contained
     * in a newly added Step object is gathered in this method. The following process
     * will occur:
     *
     * * The Step object is inserted in the Steps array for storage.
     * * The AssessmentItemRef is added to the occurence map.
     * * The AssessmentItemRef is added to the category map.
     * * The AssessmentItemRef is added to the section map.
     *
     * @param Step $step
     */
    protected function registerAssessmentItemRef(Step $step) {
        array_push($this->steps, $step);
    
        // For more convenience ;)
        $assessmentItemRef = $step->getAssessmentItemOccurence()->getAssessmentItemRef();
    
        // Count the number of occurences for the assessmentItemRef.
        if (isset($this->assessmentItemRefOccurenceCount[$assessmentItemRef]) === false) {
            $this->assessmentItemRefOccurenceCount[$assessmentItemRef] = 0;
        }
    
        $this->assessmentItemRefOccurenceCount[$assessmentItemRef] += 1;
        $step->getAssessmentItemOccurence()->setOccurence($this->assessmentItemRefOccurenceCount[$assessmentItemRef] - 1);
    
        // Reference the assessmentItemRef object of the Step
        // for a later use.
        $this->assessmentItemRefs->attach($assessmentItemRef);
    
        // Reference the assessmentItemRef object of the Step
        // by category for a later use.
        foreach ($assessmentItemRef->getCategories() as $category) {
            if (isset($this->assessmentItemRefCategoryMap[$category]) === false) {
                $this->assessmentItemRefCategoryMap[$category] = new AssessmentItemRefCollection();
            }
            $this->assessmentItemRefCategoryMap[$category][] = $assessmentItemRef;
    
            if ($this->categories->contains($category) === false) {
                $this->categories[] = $category;
            }
        }
    
        // Reference the AssessmentItemRef object of the Step
        // by section for a later use.
        foreach ($step->getAssessmentSections() as $s) {
            $assessmentSectionIdentifier = $s->getIdentifier();
            if (isset($this->assessmentItemRefSectionMap[$assessmentSectionIdentifier]) === false) {
                $this->assessmentItemRefSectionMap[$assessmentSectionIdentifier] = new AssessmentItemRefCollection();
            }
            $this->assessmentItemRefSectionMap[$assessmentSectionIdentifier][] = $assessmentItemRef;
        }
    
        // Reference the AssessmentItemRef by Step.
        if (isset($this->assessmentItemRefMap[$assessmentItemRef]) === false) {
            $this->assessmentItemRefMap[$assessmentItemRef] = new StepCollection();
        }
        $this->assessmentItemRefMap[$assessmentItemRef][] = $step;
    }
    
    /**
     * Register all needed information about the TestPart involved in a given
     * $step.
     *
     * @param Step $step A Step object.
     */
    protected function registerTestPart(Step $step) {
        // Register the RouteItem in the testPartMap.
        $testPart = $step->getTestPart();
    
        if (isset($this->testPartMap[$testPart]) === false) {
            $this->testPartMap[$testPart] = array();
        }
    
        $target = $this->testPartMap[$testPart];
        $target[] = $step;
        $this->testPartMap[$testPart] = $target;
    
        // Register the Step in the testPartIdentifierMap.
        $id = $testPart->getIdentifier();
    
        if (isset($this->testPartIdentifierMap[$id]) === false) {
            $this->testPartIdentifierMap[$id] = array();
        }
    
        $this->testPartIdentifierMap[$id][] = $step;
    }
    
    /**
     * Register all needed information about the AssessmentSection involved in a given
     * $step.
     * 
     * @param Step $step A Step object.
     */
    protected function registerAssessmentSection(Step $step) {
        
        foreach ($step->getAssessmentSections() as $assessmentSection) {
            
            if (isset($this->assessmentSectionMap[$assessmentSection]) === false) {
                $this->assessmentSectionMap[$assessmentSection] = array();
            }
            
            $target = $this->assessmentSectionMap[$assessmentSection];
            $target[] = $step;
            $this->assessmentSectionMap[$assessmentSection] = $target;
            
            // Register the Step in the assessmentSectionIdentifierMap.
            $id = $assessmentSection->getIdentifier();
            
            if (isset($this->assessmentSectionIdentifierMap[$id]) === false) {
                $assessmentSectionIdentifierMap[$id] = array();
            }
            
            $this->assessmentSectionIdentifierMap[$id][] = $step;
        }
    }
    
    /**
     * Get the sequence of identifiers formed by the identifiers of each
     * StepAssessmentItemRef object of the route, in the order they must be taken.
     *
     * @param boolean $withSequenceNumber Whether to return the sequence number in the identifier or not.
     * @return IdentifierCollection
     */
    public function getIdentifierSequence($withSequenceNumber = true) {
        $steps = &$this->getSteps();
        $collection = new IdentifierCollection();
    
        foreach (array_keys($steps) as $k) {
            $occurence = $steps[$k]->getAssessmentItemOccurence();
            $virginIdentifier = $occurence->getAssessmentItemRef()->getIdentifier();
            $collection[] = ($withSequenceNumber === true) ? $virginIdentifier . '.' . ($occurence->getOccurence() + 1) : $virginIdentifier;
        }
    
        return $collection;
    }
    
    /**
     * Get the StepAssessmentItemRef objects involved in the route that belong
     * to a given $category.
     *
     * If no StepAssessmentItemRef involved in the route are found for the given $category,
     * the return StepAssessmentItemRefCollection is empty.
     *
     * @param string|IdentifierCollection $category A category identifier.
     * @return StepAssessmentItemRefCollection An collection of StepAssessmentItemRefCollection that belong to $category.
     */
    public function getAssessmentItemRefsByCategory($category) {
    
        $categoryMap = $this->getAssessmentItemRefCategoryMap();
        $categories = (gettype($category) === 'string') ? array($category) : $category->getArrayCopy();
    
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
     * Get a subset of StepAssessmentItemRef objects by $sectionIdentifier. If no items are matching $sectionIdentifier,
     * an empty collection is returned.
     *
     * @param string $sectionIdentifier A section identifier.
     * @return StepAssessmentItemRefCollection A Collection of StepAssessmentItemRef objects that belong to the section $sectionIdentifier.
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
     * Get a subset of StepAssessmentItemRef objects. The criterias are the $sectionIdentifier
     * and categories to be included/excluded.
     *
     * @param string $sectionIdentifier The identifier of the section.
     * @param IdentifierCollection $includeCategories A collection of category identifiers to be included in the selection.
     * @param IdentifierCollection $excludeCategories A collection of category identifiers to be excluded from the selection.
     * @return StepAssessmentItemRefCollection A collection of filtered StepAssessmentItemRef objects.
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
     * @param StepAssessmentItemRef $assessmentItemRef A StepAssessmentItemRef object.
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
     * Get the number of Step objects held by the Route.
     *
     * @return integer
     */
    public function count() {
        return count($this->getSteps());
    }
    
    /**
     * Get a Step object at $position in the route sequence. Please be careful that the route sequence index
     * begins at 0. In other words, the first step in the sequence will be found at position 0, the second
     * at position 1, ...
     *
     * @param integer $position The position of the requested Step object in the route sequence.
     * @return Step The Step found at $position.
     * @throws OutOfBoundsException If no Step is found at $position.
     */
    public function getStepAt($position) {
        $steps = &$this->getSteps();
    
        if (isset($steps[$position]) === true) {
            return $steps[$position];
        }
        else {
            $msg = "No Step object found at position '${position}'.";
            throw new OutOfBoundsException($msg);
        }
    }
    
    /**
     * Get the last Step object composing the Route.
     *
     * @return Step The last Step of the Route.
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function getLastStep() {
        $steps = &$this->getSteps();
        $stepsCount = count($steps);
    
        if ($stepsCount === 0) {
            $msg = "Cannot get the last Step of the Route while it is empty.";
            throw new OutOfBoundsException($msg);
        }
    
        return $steps[$stepsCount - 1];
    }
    
    /**
     * Get the first Step object composing the Route.
     *
     * @throws OutOfBoundsException If the Route is empty.
     * @return Step The first Step of the Route.
     */
    public function getFirstStep() {
        $steps = &$this->getSteps();
        $stepsCount = count($steps);
    
        if ($stepsCount === 0) {
            $msg = "Cannot get the first Step of the Route while it is empty.";
            throw new OutOfBoundsException($msg);
        }
    
        return $steps[0];
    }
    
    /**
     * Whether the current Step is the last of the current TestPart.
     *
     * @return boolean
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function isLastOfTestPart() {
        $count = $this->count();
        if ($count === 0) {
            $msg = "Cannot determine if the current Step is the last of its TestPart when the Route is empty.";
            throw new OutOfBoundsException($msg);
        }
    
        $nextPosition = $this->getPosition() + 1;
        if ($nextPosition >= $count) {
            // This is the last routeitem of the whole route.
            return true;
        }
        else {
            $currentTestPart = $this->current()->getTestPart();
            $nextTestPart = $this->getStepAt($nextPosition)->getTestPart();
    
            return $currentTestPart !== $nextTestPart;
        }
    }
    
    /**
     * Whether the current Step is the first of the current TestPart.
     *
     * @return boolean
     * @throws OutOfBoundsException If the Route is empty.
     */
    public function isFirstOfTestPart() {
        $count = $this->count();
        if ($count === 0) {
            $msg = "Cannot determine if the current RouteItem is the first of its TestPart when the Route is empty.";
            throw new OutOfBoundsException($msg);
        }
    
        $previousPosition = $this->getPosition() - 1;
        if ($previousPosition === 0) {
            // This is the very first RouteItem of the whole Route.
            return true;
        }
        else if ($this->getStepAt($previousPosition)->getTestPart() !== $this->current()->getTestPart()) {
            return true;
        }
        else {
            return false;
        }
    }
    
    /**
     * Get the previous Step in the route.
     *
     * @return Step The previous Step in the Route.
     * @throws OutOfBoundsException If there is no previous Step in the route. In other words, the current Step in the route is the first one of the sequence.
     */
    public function getPrevious() {
        $currentPosition = $this->getPosition();
        if ($currentPosition === 0) {
            $msg = "The current Step is the first one in the route. There is no previous Step";
            throw new OutOfBoundsException($msg);
        }
    
        return $this->getStepAt($currentPosition - 1);
    }
    
    /**
     * Get the next Step in the route.
     *
     * @return Step The previous RouteItem in the Route.
     * @throws OutOfBoundsException If there is no next Step in the route. In other words, the current Step in the route is the last one of the sequence.
     */
    public function getNext() {
        if ($this->isLast() === true) {
            $msg = "The current Step is the last one in the route. There is no next Step.";
            throw new OutOfBoundsException($msg);
        }
    
        return $this->getStepAt($this->getPosition() + 1);
    }
    
    /**
     * Whether the Step at $position in the Route is in the given $testPart.
     *
     * @param integer $position A position in the Route sequence.
     * @param StepTestPart $testPart A StepTestPart object involved in the Route.
     * @return boolean
     * @throws OutOfBoundsException If $position is out of the Route bounds.
     */
    public function isInTestPart($position, TestPart $testPart) {
        try {
            $step = $this->getStepAt($position);
            return $step->getTestPart() === $testPart;
        }
        catch (OutOfBoundsException $e) {
            // The position does not refer to any Step. This is out of the bounds of the route.
            $msg = "The position '${position}' is out of the bounds of the Route.";
            throw new OutOfBoundsException($msg, 0, $e);
        }
    }
    
    /**
     * Get the Step objects involved in the current TestPart.
     *
     * @return StepCollection A collection of Step objects involved in the current TestPart.
     */
    public function getCurrentTestPartSteps() {
        return $this->getStepsByTestPart($this->current()->getTestPart());
    }
    
    /**
     * Get the Step objects involved in a given test part.
     *
     * @param string|StepTestPart An identifier or a TestPart object.
     * @return StepCollection A collection of Step objects involved in the current TestPart.
     * @throws OutOfBoundsException If $testPart is not referenced in the Route.
     * @throws OutOfRangeException If $testPart is not a string nor a TestPart object.
     */
    public function getStepsByTestPart($testPart) {
    
        if (gettype($testPart) === 'string') {
            $map = $this->getTestPartIdentifierMap();
    
            if (isset($map[$testPart]) === false) {
                $msg = "No testPart with identifier '${testPart}' is referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }
        
            return new StepCollection($map[$testPart]);
        }
        else if ($testPart instanceof TestPart) {
            $map = $this->getTestPartMap();
        
            if (isset($map[$testPart]) === false) {
                $msg = "The testPart '" . $testPart->getIdentifier() . "' is not referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }
        
            return new StepCollection($map[$testPart]);
        }
        else {
            $msg = "The 'testPart' argument must be a string or a TestPart object.";
            throw new OutOfRangeException($msg);
        }
    }
    
    /**
     * Get the Step objects involved in a given StepAssessmentSection.
     *
     * @param string|StepAssessmentSection $assessmentSection A StepAssessmentSection object or an identifier.
     * @return StepCollection A collection of Step objects involved in $assessmentSection.
     * @throws OutOfBoundsException If $assessmentSection is not referenced in the Route.
     * @throws OutOfRangeException If $assessmentSection is not a string nor an AssessmentSection object.
     */
    public function getStepsByAssessmentSection($assessmentSection) {
    
        if (gettype($assessmentSection) === 'string') {
            $map = $this->getAssessmentSectionIdentifierMap();
    
            if (isset($map[$assessmentSection]) === false) {
                $msg = "No assessmentSection with identifier '${assessmentSection}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }
        
            return new StepCollection($map[$assessmentSection]);
        }
        else if ($assessmentSection instanceof AssessmentSection) {
            $map = $this->getAssessmentSectionMap();
            $steps = new StepCollection();
        
            if (isset($map[$assessmentSection]) === false) {
                $msg = "The assessmentSection '" . $assessmentSection->getIdentifier() . "' is not referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }
        
            return new StepCollection($map[$assessmentSection]);
        }
        else {
            $msg = "The 'assessmentSection' argument must be a string or an AssessmentSection object.";
            throw new OutOfRangeException($msg);
        }
    }
    
    /**
     * Get the Step object involved in a given StepAssessmentItemRef.
     *
     * @param string|StepAssessmentItemRef $assessmentItemRef A StepAssessmentItemRef object or an identifier.
     * @throws OutOfBoundsException If $assessmentItemRef is not referenced in the Route.
     * @throws OutOfRangeException If $assessmentItemRef is not a string nor a StepAssessmentItemRef object.
     * @return StepCollection A collection of Step objects involved in $assessmentItemRef.
     */
    public function getStepsByAssessmentItemRef($assessmentItemRef) {
    
        if (gettype($assessmentItemRef) === 'string') {
    
            if (($ref = $this->assessmentItemRefs[$assessmentItemRef]) !== null) {
                return $this->assessmentItemRefMap[$ref];
            }
            else {
                $msg = "No AssessmentItemRef with identifier '${assessmentItemRef}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }
        
        }
        else if ($assessmentItemRef instanceof AssessmentItemRef) {
        
            if (isset($this->assessmentItemRefMap[$assessmentItemRef]) === true) {
                return $this->assessmentItemRefMap[$assessmentItemRef];
            }
            else {
                $msg = "No AssessmentItemRef with 'identifier' ${assessmentItemRef}' found in the Route.";
                throw new OutOfBoundsException($msg);
        }
        }
        else {
            $msg = "The 'assessmentItemRef' argument must be a string or an AssessmentItemRef object.";
            throw new OutOfRangeException($msg);
        }
    }
    
    /**
     * Get all the Step objects composing the Route.
     *
     * @return StepCollection A collection of Step objects.
     */
    public function getAllSteps() {
        return new StepCollection($this->getSteps());
    }
    
    /**
     * Perform a branching on a TestPart, AssessmentSection or AssessmentItemRef with
     * the given $identifier.
     *
     * @param string $identifier A QTI Identifier to be the target of the branching.
     * @throws OutOfBoundsException If an error occurs while branching e.g. the $identifier is not referenced in the route.
     * @throws OutOfRangeException If $identifier is not a valid branching identifier.
     */
    public function branch($identifier) {
    
        try {
            $identifier = new VariableIdentifier($identifier);
    
            $id = ($identifier->hasPrefix() === false) ? $identifier->getVariableName() : $identifier->getPrefix();
            $occurence = ($identifier->hasPrefix() === false) ? 0 : intval($identifier->getVariableName() - 1);
        }
        catch (\InvalidArgumentException $e) {
            $msg = "Branch failed: the given identifier '${identifier}' is an invalid branching target.";
            throw new OutOfRangeException($msg);
        }
        
        // Check for an assessmentItemRef.
        $assessmentItemRefs = $this->getAssessmentItemRefs();
        if (isset($assessmentItemRefs[$id]) === true) {
        
            $assessmentItemRefMap = $this->getAssessmentItemRefMap();
            $targetSteps = $assessmentItemRefMap[$assessmentItemRefs[$id]];
        
            if ($targetSteps[$occurence]->getTestPart() !== $this->current()->getTestPart()) {
                // From IMS QTI:
                // In case of an item or section, the target must refer to an item or section
                // in the same testPart [...]
                $msg = "Branching to '${id}' failed: branch to items outside of the current testPart is forbidden by the QTI 2.1 specification.";
                throw new OutOfBoundsException($msg);
            }
        
            $this->setPosition($this->getStepPosition($targetSteps[$occurence]));
            return;
        }
        
        // Check for a assessmentSection.
        $assessmentSectionIdentifierMap = $this->getAssessmentSectionIdentifierMap();
        if (isset($assessmentSectionIdentifierMap[$id]) === true) {
        
            if ($assessmentSectionIdentifierMap[$id][0]->getTestPart() !== $this->current()->getTestPart()) {
                // From IMS QTI:
                // In case of an item or section, the target must refer to an item or section
                // in the same testPart [...]
                $msg = "Branching to '${id}' failed: branch to assessmentSections outside of the current testPart is forbidden by the QTI 2.1 specification.";
                throw new OutOfBoundsException($msg);
            }
        
            // We branch to the first Step belonging to the section.
            $this->setPosition($this->getStepPosition($assessmentSectionIdentifierMap[$id][0]));
            return;
        }
        
        // Check for a testPart.
        $testPartIdentifierMap = $this->getTestPartIdentifierMap();
        if (isset($testPartIdentifierMap[$id]) === true) {
        
            // We branch to the first Step belonging to the testPart.
            if ($testPartIdentifierMap[$id][0]->getTestPart() === $this->current()->getTestPart()) {
                // From IMS QTI:
                // For testParts, the target must refer to another testPart.
                $msg = "Branching to '${id}' failed: branch to the same testPart as the current one is forbidden by the QTI 2.1 specification.";
                throw new OutOfBoundsException($msg);
            }
        
            // We branch to the first Step belonging to the testPart.
            $this->setPosition($this->getStepPosition($testPartIdentifierMap[$id][0]));
            return;
        }
        
        // No such identifier referenced in the route, cannot branch.
        $msg = "Branching to '${id}' failed: No such identifier found in the route for branching.";
        throw new OutOfBoundsException($msg);
    }
    
    /**
     * Get the position of $step in the Route.
     *
     * @param Step $ste A Step you want to know the position in the Route.
     * @throws OutOfBoundsException If no such $step is referenced in the Route.
     * @return integer The position of the Step in the Route. The indexes begin at 0.
     */
    public function getStepPosition(Step $step) {
        if (($search = array_search($step, $this->getSteps(), true)) !== false) {
            return $search;
        }
        else {
            $msg = "No such Step object referenced in the Route.";
            throw new OutOfBoundsException($msg);
        }
    }
}