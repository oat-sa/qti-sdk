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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 *
 */

namespace qtism\runtime\tests;

use qtism\data\rules\PreConditionCollection;
use qtism\data\TestPartCollection;
use qtism\runtime\common\VariableIdentifier;
use qtism\data\SubmissionMode;
use qtism\data\NavigationMode;
use qtism\data\AssessmentItemRefCollection;
use qtism\common\collections\IdentifierCollection;
use qtism\data\AssessmentTest;
use qtism\data\TestPart;
use qtism\data\AssessmentItemRef;
use qtism\data\AssessmentSection;
use qtism\data\AssessmentSectionCollection;
use \Iterator;
use \SplObjectStorage;
use \OutOfBoundsException;
use \OutOfRangeException;
use \InvalidArgumentException;
use qtism\runtime\expressions\ExpressionEngine;

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
class Route implements Iterator
{
    /**
     * A collection that gathers all assessmentItemRefs
     * involved in the route.
     *
     * @var \qtism\data\AssessmentItemRefCollection
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
     * @var \SplObjectStorage
     */
    private $assessmentItemRefOccurenceCount;

    /**
     * A map where each RouteItem is bound to a test part.
     *
     * @var \SplObjectStorage
     */
    private $testPartMap;

    /**
     * A map where each RouteItem is bound to a test part identifier.
     *
     * @var array
     */
    private $testPartIdentifierMap;

    /**
     * A map where each RouteItem is bound to an assessment section.
     *
     * @var \SplObjectStorage
     */
    private $assessmentSectionMap;

    /**
     * A map where each RouteItem is bound to an assessment section identifier.
     *
     * @var array
     */
    private $assessmentSectionIdentifierMap;

    /**
     * A map where each RouteItem is bound to an assessmentItemRef.
     *
     * @var \SplObjectStorage
     */
    private $assessmentItemRefMap;

    /**
     * The RouteItem objects the Route is composed with.
     *
     * @var array
     */
    private $routeItems = [];

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
     * @var \qtism\common\collections\IdentifierCollection
     */
    private $categories;

    /**
     * Create a new Route object.
     *
     */
    public function __construct()
    {
        $this->setPosition(0);
        $this->setAssessmentItemRefs(new AssessmentItemRefCollection());
        $this->setAssessmentItemRefCategoryMap([]);
        $this->setAssessmentItemRefSectionMap([]);
        $this->setAssessmentItemRefOccurenceMap(new SplObjectStorage());
        $this->setCategories(new IdentifierCollection());
        $this->setTestPartMap(new SplObjectStorage());
        $this->setAssessmentSectionMap(new SplObjectStorage());
        $this->setTestPartIdentifierMap([]);
        $this->setAssessmentSectionIdentifierMap([]);
        $this->setAssessmentItemRefMap(new SplObjectStorage());
    }

    /**
     * Get the current index position.
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set the current index position.
     *
     * @param integer $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * Get a reference on the RouteItem objects contained
     * within this Route object.
     *
     * @return array
     */
    protected function &getRouteItems()
    {
        return $this->routeItems;
    }

    /**
     * Get the collection of AssessmentItemRef objects
     * that are involded in the route.
     *
     * @return \qtism\data\AssessmentItemRefCollection A collection of AssessmentItemRef objects.
     */
    public function getAssessmentItemRefs()
    {
        return $this->assessmentItemRefs;
    }

    /**
     * Set the collection of AssessmentItemRef objects that are involved
     * in this route.
     *
     * @param \qtism\data\AssessmentItemRefCollection $assessmentItemRefs A collection of AssessmentItemRefObjects.
     */
    public function setAssessmentItemRefs(AssessmentItemRefCollection $assessmentItemRefs)
    {
        $this->assessmentItemRefs = $assessmentItemRefs;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are
     * stored by category.
     *
     * @return array A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same category.
     */
    protected function getAssessmentItemRefCategoryMap()
    {
        return $this->assessmentItemRefCategoryMap;
    }

    /**
     * Set the map where AssessmentItemRef objects involved in the route are stored
     * by category.
     *
     * @param array $assessmentItemRefCategoryMap A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef object of the same category.
     */
    protected function setAssessmentItemRefCategoryMap(array $assessmentItemRefCategoryMap)
    {
        $this->assessmentItemRefCategoryMap = $assessmentItemRefCategoryMap;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * by section.
     *
     * @return array A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same section.
     */
    protected function getAssessmentItemRefSectionMap()
    {
        return $this->assessmentItemRefSectionMap;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * by section.
     *
     * @param array $assessmentItemRefSectionMap A map of AssessmentItemRefCollection objects, which contain AssessmentItemRef objects of the same section.
     */
    protected function setAssessmentItemRefSectionMap(array $assessmentItemRefSectionMap)
    {
        $this->assessmentItemRefSectionMap = $assessmentItemRefSectionMap;
    }

    /**
     * Get the map where AssessmentItemRef objects involved in the route are stored
     * with a number of occurence.
     *
     * @return \SplObjectStorage
     */
    protected function getAssessmentItemRefOccurenceMap()
    {
        return $this->assessmentItemRefOccurenceCount;
    }

    /**
     * Set the map where AssessmentItemRef objects involved in the route are stored
     * with a number of occurence.
     *
     * @param \SplObjectStorage $assessmentItemRefOccurenceCount
     */
    protected function setAssessmentItemRefOccurenceMap(SplObjectStorage $assessmentItemRefOccurenceCount)
    {
        $this->assessmentItemRefOccurenceCount = $assessmentItemRefOccurenceCount;
    }

    /**
     * Set the map where RouteItem objects are gathered by TestPart.
     *
     * @param \SplObjectStorage $testPartMap
     */
    protected function setTestPartMap(SplObjectStorage $testPartMap)
    {
        $this->testPartMap = $testPartMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by TestPart.
     *
     * @return \SplObjectStorage
     */
    protected function getTestPartMap()
    {
        return $this->testPartMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by TestPart identifier.
     *
     * @param array $testPartIdentifierMap
     */
    protected function setTestPartIdentifierMap(array $testPartIdentifierMap)
    {
        $this->testPartIdentifierMap = $testPartIdentifierMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by TestPart identifier.
     *
     * @return array
     */
    protected function getTestPartIdentifierMap()
    {
        return $this->testPartIdentifierMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by AssessmentSection.
     *
     * @param \SplObjectStorage $assessmentSectionMap
     */
    protected function setAssessmentSectionMap(SplObjectStorage $assessmentSectionMap)
    {
        $this->assessmentSectionMap = $assessmentSectionMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by AssessmentSection.
     *
     * @return \SplObjectStorage
     */
    protected function getAssessmentSectionMap()
    {
        return $this->assessmentSectionMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by AssessmentSection identifier.
     *
     * @param array $assessmentSectionIdentifierMap
     */
    protected function setAssessmentSectionIdentifierMap(array $assessmentSectionIdentifierMap)
    {
        $this->assessmentSectionIdentifierMap = $assessmentSectionIdentifierMap;
    }

    /**
     * Get the map where RouteItems objects are gathered by AssessmentSection identifier.
     *
     * @return array
     */
    protected function getAssessmentSectionIdentifierMap()
    {
        return $this->assessmentSectionIdentifierMap;
    }

    /**
     * Set the map where RouteItem objects are gathered by AssessmentItemRef objects.
     *
     * @param \SplObjectStorage $assessmentItemRefMap
     */
    protected function setAssessmentItemRefMap(SplObjectStorage $assessmentItemRefMap)
    {
        $this->assessmentItemRefMap = $assessmentItemRefMap;
    }

    /**
     * Get the map where RouteItem objects are gathered by AssessmentItemRef objects.
     *
     * @return \SplObjectStorage
     */
    protected function getAssessmentItemRefMap()
    {
        return $this->assessmentItemRefMap;
    }

    /**
     * Set the collection of item categories involved in the route.
     *
     * @param \qtism\common\collections\IdentifierCollection $categories A collection of QTI Identifiers.
     */
    protected function setCategories(IdentifierCollection $categories)
    {
        $this->categories = $categories;
    }

    /**
     * Get the collection of item categories involved in the route.
     *
     * @return \qtism\common\collections\IdentifierCollection A collection of QTI Identifiers.
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * Add a new RouteItem object at the end of the Route.
     *
     * @param \qtism\data\AssessmentItemRef $assessmentItemRef
     * @param \qtism\data\AssessmentSection|\qtism\data\AssessmentSectionCollection $assessmentSections
     * @param \qtism\data\TestPart $testPart
     * @param \qtism\data\AssessmentTest $assessmentTest
     */
    public function addRouteItem(
        AssessmentItemRef $assessmentItemRef,
        $assessmentSections,
        TestPart $testPart,
        AssessmentTest $assessmentTest
    )
    {
        // Push the routeItem in the track :) !
        $routeItem = new RouteItem($assessmentItemRef, $assessmentSections, $testPart, $assessmentTest);
        $this->registerAssessmentItemRef($routeItem);
        $this->registerTestPart($routeItem);
        $this->registerAssessmentSection($routeItem);
    }

    /**
     * Add a new RouteItem object at the end of the Route.
     *
     * @param \qtism\runtime\tests\RouteItem $routeItem A RouteItemObject.
     */
    public function addRouteItemObject(RouteItem $routeItem)
    {
        $this->registerAssessmentItemRef($routeItem);
        $this->registerTestPart($routeItem);
        $this->registerAssessmentSection($routeItem);
    }

    /**
     *
     * @see Iterator::rewind()
     */
    public function rewind()
    {
        $this->setPosition(0);
    }

    /**
     * Get the current RouteItem object.
     *
     * @return \qtism\runtime\tests\RouteItem A RouteItem object.
     */
    public function current()
    {
        $routeItems = &$this->getRouteItems();

        return $routeItems[$this->getPosition()];
    }

    /**
     * Get the current key corresponding to the current RouteItem object.
     *
     * @return integer The returned key is the position of the current RouteItem object in the Route.
     */
    public function key()
    {
        return $this->getPosition();
    }

    /**
     * Set the Route as its previous position in the RouteItem sequence. If the current
     * RouteItem is the first one prior to call next(), the Route remains in the same position.
     *
     */
    public function previous()
    {
        $position = $this->getPosition();
        if ($position > 0) {
            $this->setPosition(--$position);
        }
    }

    /**
     * Set the Route as its next position in the RouteItem sequence. If the current
     * RouteItem is the last one prior to call next(), the iterator becomes invalid.
     */
    public function next()
    {
        $this->setPosition($this->getPosition() + 1);
    }

    /**
     * Whether the Route is still valid while iterating.
     *
     * @return boolean
     */
    public function valid()
    {
        $routeItems = &$this->getRouteItems();

        return isset($routeItems[$this->getPosition()]);
    }

    /**
     * Whether the current RouteItem is the last of the route.
     *
     * @return boolean
     */
    public function isLast()
    {
        $nextPosition = $this->getPosition() + 1;
        $routeItems = &$this->getRouteItems();

        return !isset($routeItems[$nextPosition]);
    }

    /**
     * Whether the current RouteItem is the first of the route.
     *
     * @return boolean
     */
    public function isFirst()
    {
        return $this->getPosition() === 0;
    }

    /**
     * Whether the current RouteItem in the route is in linear
     * navigation mode.
     *
     * @return boolean
     */
    public function isNavigationLinear()
    {
        return $this->current()->getTestPart()->getNavigationMode() === NavigationMode::LINEAR;
    }

    /**
     * Whether the current RouteItem in the route is in non-linear
     * navigation mode.
     *
     * @return boolean
     */
    public function isNavigationNonLinear()
    {
        return !$this->isNavigationLinear();
    }

    /**
     * Whether the current RouteItem in the route is in individual
     * submission mode.
     *
     * @return boolean
     */
    public function isSubmissionIndividual()
    {
        return $this->current()->getTestPart()->getSubmissionMode() === SubmissionMode::INDIVIDUAL;
    }

    /**
     * Whether the current RouteItem in the route is in simultaneous
     * submission mode.
     *
     * @return boolean
     */
    public function isSubmissionSimultaneous()
    {
        return !$this->isSubmissionIndividual();
    }

    /**
     * Append all the RouteItem objects contained in $route
     * to this Route.
     *
     * @param \qtism\runtime\tests\Route $route A Route object.
     */
    public function appendRoute(Route $route)
    {
        foreach ($route as $routeItem) {

            // @todo find why it must be cloned, I can't remember.
            $clone = clone $routeItem;

            $this->registerAssessmentItemRef($clone);
            $this->registerTestPart($clone);
            $this->registerAssessmentSection($clone);
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
     * @param \qtism\runtime\tests\RouteItem $routeItem
     */
    protected function registerAssessmentItemRef(RouteItem $routeItem)
    {
        array_push($this->routeItems, $routeItem);

        // For more convenience ;)
        $assessmentItemRef = $routeItem->getAssessmentItemRef();

        // Count the number of occurences for the assessmentItemRef.
        if (isset($this->assessmentItemRefOccurenceCount[$assessmentItemRef]) === false) {
            $this->assessmentItemRefOccurenceCount[$assessmentItemRef] = 0;
        }

        $this->assessmentItemRefOccurenceCount[$assessmentItemRef] += 1;
        $routeItem->setOccurence($this->assessmentItemRefOccurenceCount[$assessmentItemRef] - 1);

        // Reference the assessmentItemRef object of the RouteItem
        // for a later use.
        $this->assessmentItemRefs->attach($assessmentItemRef);

        // Reference the assessmentItemRef object of the RouteItem
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

        // Reference the AssessmentItemRef object of the RouteItem
        // by section for a later use.
        foreach ($routeItem->getAssessmentSections() as $s) {
            $assessmentSectionIdentifier = $s->getIdentifier();
            if (isset($this->assessmentItemRefSectionMap[$assessmentSectionIdentifier]) === false) {
                $this->assessmentItemRefSectionMap[$assessmentSectionIdentifier] = new AssessmentItemRefCollection();
            }
            $this->assessmentItemRefSectionMap[$assessmentSectionIdentifier][] = $assessmentItemRef;
        }

        // Reference the AssessmentItemRef by routeItem.
        if (isset($this->assessmentItemRefMap[$assessmentItemRef]) === false) {
            $this->assessmentItemRefMap[$assessmentItemRef] = new RouteItemCollection();
        }
        $this->assessmentItemRefMap[$assessmentItemRef][] = $routeItem;
    }

    /**
     * Register all needed information about the TestPart involved in a given
     * $routeItem.
     *
     * @param \qtism\runtime\tests\RouteItem $routeItem A RouteItem object.
     */
    protected function registerTestPart(RouteItem $routeItem)
    {
        // Register the RouteItem in the testPartMap.
        $testPart = $routeItem->getTestPart();

        if (isset($this->testPartMap[$testPart]) === false) {
            $this->testPartMap[$testPart] = [];
        }

        $target = $this->testPartMap[$testPart];
        $target[] = $routeItem;
        $this->testPartMap[$testPart] = $target;

        // Register the RouteItem in the testPartIdentifierMap.
        $id = $testPart->getIdentifier();

        if (isset($this->testPartIdentifierMap[$id]) === false) {
            $this->testPartIdentifierMap[$id] = [];
        }

        $this->testPartIdentifierMap[$id][] = $routeItem;
    }

    /**
     * Register all needed information about the AssessmentSection involved in a given
     * $routeItem.
     *
     * @param \qtism\runtime\tests\RouteItem $routeItem A RouteItem object.
     */
    protected function registerAssessmentSection(RouteItem $routeItem)
    {
        foreach ($routeItem->getAssessmentSections() as $assessmentSection) {

            if (isset($this->assessmentSectionMap[$assessmentSection]) === false) {
                $this->assessmentSectionMap[$assessmentSection] = [];
            }

            $target = $this->assessmentSectionMap[$assessmentSection];
            $target[] = $routeItem;
            $this->assessmentSectionMap[$assessmentSection] = $target;

            // Register the RouteItem in the assessmentSectionIdentifierMap.
            $id = $assessmentSection->getIdentifier();

            if (isset($this->assessmentSectionIdentifierMap[$id]) === false) {
                $assessmentSectionIdentifierMap[$id] = [];
            }

            $this->assessmentSectionIdentifierMap[$id][] = $routeItem;
        }
    }

    /**
     * Get the sequence of identifiers formed by the identifiers of each
     * assessmentItemRef object of the route, in the order they must be taken.
     *
     * @param boolean $withSequenceNumber Whether to return the sequence number in the identifier or not.
     * @return \qtism\common\collections\IdentifierCollection
     */
    public function getIdentifierSequence($withSequenceNumber = true)
    {
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
     * @return \qtism\data\AssessmentItemRefCollection An collection of AssessmentItemRefCollection that belong to $category.
     */
    public function getAssessmentItemRefsByCategory($category)
    {
        $categoryMap = $this->getAssessmentItemRefCategoryMap();
        $categories = (gettype($category) === 'string') ? [$category] : $category->getArrayCopy();

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
     * @return \qtism\data\AssessmentItemRefCollection A Collection of AssessmentItemRef objects that belong to the section $sectionIdentifier.
     */
    public function getAssessmentItemRefsBySection($sectionIdentifier)
    {
        $sectionMap = $this->getAssessmentItemRefSectionMap();

        if (isset($sectionMap[$sectionIdentifier])) {
            return $sectionMap[$sectionIdentifier];
        } else {
            return new AssessmentItemRefCollection();
        }
    }

    /**
     * Get a subset of AssessmentItemRef objects. The criterias are the $sectionIdentifier
     * and categories to be included/excluded.
     *
     * @param string $sectionIdentifier The identifier of the section.
     * @param \qtism\common\collections\IdentifierCollection $includeCategories A collection of category identifiers to be included in the selection.
     * @param \qtism\common\collections\IdentifierCollection $excludeCategories A collection of category identifiers to be excluded from the selection.
     * @return \qtism\data\AssessmentItemRefCollection A collection of filtered AssessmentItemRef objects.
     *
     */
    public function getAssessmentItemRefsSubset(
        $sectionIdentifier = '',
        IdentifierCollection $includeCategories = null,
        IdentifierCollection $excludeCategories = null
    )
    {
        $bySection = (empty($sectionIdentifier) === true) ? $this->getAssessmentItemRefs() : $this->getAssessmentItemRefsBySection($sectionIdentifier);

        if (is_null($includeCategories) === false) {
            // We will perform the search by category inclusion.
            return $bySection->intersect($this->getAssessmentItemRefsByCategory($includeCategories));
        } elseif (is_null($excludeCategories) === false) {
            // Perform the category by exclusion.
            return $bySection->diff($this->getAssessmentItemRefsByCategory($excludeCategories));
        } else {
            return $bySection;
        }
    }

    /**
     * Get the number of occurences found in the route for the given $assessmentItemRef.
     * If $assessmentItemRef is not involved in the route, the returned result is 0.
     *
     * @param \qtism\data\AssessmentItemRef $assessmentItemRef An AssessmentItemRef object.
     * @return integer The number of occurences found in the route for $assessmentItemRef.
     */
    public function getOccurenceCount(AssessmentItemRef $assessmentItemRef)
    {
        $occurenceMap = $this->getAssessmentItemRefOccurenceMap();
        if (isset($occurenceMap[$assessmentItemRef]) === true) {
            return $occurenceMap[$assessmentItemRef];
        } else {
            return 0;
        }
    }

    /**
     * Get the number of RoutItem objects held by the Route.
     *
     * @return integer
     */
    public function count()
    {
        return count($this->getRouteItems());
    }

    /**
     * Get a RouteItem object at $position in the route sequence. Please be careful that the route sequence index
     * begins at 0. In other words, the first route item in the sequence will be found at position 0, the second
     * at position 1, ...
     *
     * @param integer $position The position of the requested RouteItem object in the route sequence.
     * @return \qtism\runtime\tests\RouteItem The RouteItem found at $position.
     * @throws \OutOfBoundsException If no RouteItem is found at $position.
     */
    public function getRouteItemAt($position)
    {
        $routeItems = &$this->getRouteItems();

        if (isset($routeItems[$position]) === true) {
            return $routeItems[$position];
        } else {
            $msg = "No RouteItem object found at position '${position}'.";
            throw new OutOfBoundsException($msg);
        }
    }

    /**
     * Get the last RouteItem object composing the Route.
     *
     * @return \qtism\runtime\tests\RouteItem The last RouteItem of the Route.
     * @throws \OutOfBoundsException If the Route is empty.
     */
    public function getLastRouteItem()
    {
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
     * @throws \OutOfBoundsException If the Route is empty.
     * @return \qtism\runtime\tests\RouteItem The first RouteItem of the Route.
     */
    public function getFirstRouteItem()
    {
        $routeItems = &$this->getRouteItems();
        $routeItemsCount = count($routeItems);

        if ($routeItemsCount === 0) {
            $msg = "Cannot get the first RouteItem of the Route while it is empty.";
            throw new OutOfBoundsException($msg);
        }

        return $routeItems[0];
    }

    /**
     * Whether the current RouteItem is the last of the current TestPart.
     *
     * @return boolean
     * @throws \OutOfBoundsException If the Route is empty.
     */
    public function isLastOfTestPart()
    {
        $count = $this->count();
        if ($count === 0) {
            $msg = "Cannot determine if the current RouteItem is the last of its TestPart when the Route is empty.";
            throw new OutOfBoundsException($msg);
        }

        $nextPosition = $this->getPosition() + 1;
        if ($nextPosition >= $count) {
            // This is the last routeitem of the whole route.
            return true;
        } else {
            $currentTestPart = $this->current()->getTestPart();
            $nextTestPart = $this->getRouteItemAt($nextPosition)->getTestPart();

            return $currentTestPart !== $nextTestPart;
        }
    }

    /**
     * Whether the current RouteItem is the first of the current TestPart.
     *
     * @return boolean
     * @throws \OutOfBoundsException If the Route is empty.
     */
    public function isFirstOfTestPart()
    {
        $count = $this->count();
        if ($count === 0) {
            $msg = "Cannot determine if the current RouteItem is the first of its TestPart when the Route is empty.";
            throw new OutOfBoundsException($msg);
        }

        $previousPosition = $this->getPosition() - 1;
        if ($previousPosition === -1) {
            // This is the very first RouteItem of the whole Route.
            return true;
        } elseif ($this->getRouteItemAt($previousPosition)->getTestPart() !== $this->current()->getTestPart()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Whether the current RouteItem is the first of the current AssessmentSection set as parameter.
     *
     * @section \qtism\runtime\tests\AssessmentSection The AssessmentSection we need to check if this RouteItem
     * is first or not.
     *
     * @return boolean
     * @throws \OutOfBoundsException If the Route is empty.
     */
    public function isFirstOfSection($section)
    {
        $count = $this->count();
        if ($count === 0) {
            $msg = "Cannot determine if the current RouteItem is the first of an AssessmentSection
             when the Route is empty.";
            throw new OutOfBoundsException($msg);
        }

        if (($section == null) || (!in_array($this->current()->getAssessmentItemRef(),
                $section->getComponentsByClassName("assessmentItemRef")->getArrayCopy()))) {
            $msg = "RouteItem not in section given as parameter.";
            throw new OutOfBoundsException($msg);
        }

        $previousPosition = $this->getPosition() - 1;
        if ($previousPosition === -1) {
            // This is the very first RouteItem of the whole Route.
            return true;
        } elseif (!in_array($this->getRouteItemAt($previousPosition)->getAssessmentItemRef(),
            $section->getSectionParts()->getArrayCopy())) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the previous RouteItem in the route.
     *
     * @return \qtism\runtime\tests\RouteItem The previous RouteItem in the Route.
     * @throws \OutOfBoundsException If there is no previous RouteItem in the route. In other words, the current RouteItem in the route is the first one of the sequence.
     */
    public function getPrevious()
    {
        $currentPosition = $this->getPosition();
        if ($currentPosition === 0) {
            $msg = "The current RouteItem is the first one in the route. There is no previous RouteItem";
            throw new OutOfBoundsException($msg);
        }

        return $this->getRouteItemAt($currentPosition - 1);
    }

    /**
     * Get the next RouteItem in the route.
     *
     * @return \qtism\runtime\tests\RouteItem The previous RouteItem in the Route.
     * @throws \OutOfBoundsException If there is no next RouteItem in the route. In other words, the current RouteItem in the route is the last one of the sequence.
     */
    public function getNext()
    {
        if ($this->isLast() === true) {
            $msg = "The current RouteItem is the last one in the route. There is no next RouteItem.";
            throw new OutOfBoundsException($msg);
        }

        return $this->getRouteItemAt($this->getPosition() + 1);
    }

    /**
     * Whether the RouteItem at $position in the Route is in the given $testPart.
     *
     * @param integer $position A position in the Route sequence.
     * @param \qtism\data\TestPart $testPart A TestPart object involved in the Route.
     * @return boolean
     * @throws <OutOfBoundsException If $position is out of the Route bounds.
     */
    public function isInTestPart($position, TestPart $testPart)
    {
        try {
            $routeItem = $this->getRouteItemAt($position);

            return $routeItem->getTestPart() === $testPart;
        } catch (OutOfBoundsException $e) {
            // The position does not refer to any RouteItem. This is out of the bounds of the route.
            $msg = "The position '${position}' is out of the bounds of the route.";
            throw new OutOfBoundsException($msg, 0, $e);
        }
    }

    /**
     * Get the RouteItem objects involved in the current TestPart.
     *
     * @return \qtism\runtime\tests\RouteItemCollection A collection of RouteItem objects involved in the current TestPart.
     */
    public function getCurrentTestPartRouteItems()
    {
        return $this->getRouteItemsByTestPart($this->current()->getTestPart());
    }

    /**
     * Get the RouteItem objects involved in a given test part.
     *
     * @param string|\qtism\data\TestPart An identifier or a TestPart object.
     * @return \qtism\runtime\tests\RouteItemCollection A collection of RouteItem objects involved in the current TestPart.
     * @throws \OutOfBoundsException If $testPart is not referenced in the Route.
     * @throws \OutOfRangeException If $testPart is not a string nor a TestPart object.
     */
    public function getRouteItemsByTestPart($testPart)
    {
        if (gettype($testPart) === 'string') {
            $map = $this->getTestPartIdentifierMap();

            if (isset($map[$testPart]) === false) {
                $msg = "No testPart with identifier '${testPart}' is referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$testPart]);
        } elseif ($testPart instanceof TestPart) {
            $map = $this->getTestPartMap();

            if (isset($map[$testPart]) === false) {
                $msg = "The testPart '" . $testPart->getIdentifier() . "' is not referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$testPart]);
        } else {
            $msg = "The 'testPart' argument must be a string or a TestPart object.";
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Get the RouteItem objects involved in a given AssessmentSection.
     *
     * @param string|\qtism\data\AssessmentSection $assessmentSection An AssessmentSection object or an identifier.
     * @return \qtism\runtime\tests\RouteItemCollection A collection of RouteItem objects involved in $assessmentSection.
     * @throws \OutOfBoundsException If $assessmentSection is not referenced in the Route.
     * @throws \OutOfRangeException If $assessmentSection is not a string nor an AssessmentSection object.
     */
    public function getRouteItemsByAssessmentSection($assessmentSection)
    {
        if (gettype($assessmentSection) === 'string') {
            $map = $this->getAssessmentSectionIdentifierMap();

            if (isset($map[$assessmentSection]) === false) {
                $msg = "No assessmentSection with identifier '${assessmentSection}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$assessmentSection]);
        } elseif ($assessmentSection instanceof AssessmentSection) {
            $map = $this->getAssessmentSectionMap();
            $routeItems = new RouteItemCollection();

            if (isset($map[$assessmentSection]) === false) {
                $msg = "The assessmentSection '" . $assessmentSection->getIdentifier() . "' is not referenced in the Route.";
                throw new OutOfBoundsException($msg);
            }

            return new RouteItemCollection($map[$assessmentSection]);
        } else {
            $msg = "The 'assessmentSection' argument must be a string or an AssessmentSection object.";
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Get the RouteItem object involved in a given AssessmentItemRef.
     *
     * @param string|\qtism\data\AssessmentItemRef $assessmentItemRef An AssessmentItemRef object or an identifier.
     * @throws \OutOfBoundsException If $assessmentItemRef is not referenced in the Route.
     * @throws \OutOfRangeException If $assessmentItemRef is not a string nor an AssessmentItemRef object.
     * @return \qtism\runtime\tests\RouteItemCollection A collection of RouteItem objects involved in $assessmentItemRef.
     */
    public function getRouteItemsByAssessmentItemRef($assessmentItemRef)
    {
        if (gettype($assessmentItemRef) === 'string') {

            if (($ref = $this->assessmentItemRefs[$assessmentItemRef]) !== null) {
                return $this->assessmentItemRefMap[$ref];
            } else {
                $msg = "No AssessmentItemRef with identifier '${assessmentItemRef}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }
        } elseif ($assessmentItemRef instanceof AssessmentItemRef) {

            if (isset($this->assessmentItemRefMap[$assessmentItemRef]) === true) {
                return $this->assessmentItemRefMap[$assessmentItemRef];
            } else {
                $msg = "No AssessmentItemRef with 'identifier' ${assessmentItemRef}' found in the Route.";
                throw new OutOfBoundsException($msg);
            }
        } else {
            $msg = "The 'assessmentItemRef' argument must be a string or an AssessmentItemRef object.";
            throw new OutOfRangeException($msg);
        }
    }

    /**
     * Get all the RouteItem objects composing the Route.
     *
     * @return \qtism\runtime\tests\RouteItemCollection A collection of RouteItem objects.
     */
    public function getAllRouteItems()
    {
        return new RouteItemCollection($this->getRouteItems());
    }

    /**
     * Perform a branching on a TestPart, AssessmentSection or AssessmentItemRef with
     * the given $identifier.
     *
     * The target will be considered invalid if the following constraints are not fullfilled:
     *
     * From IMS QTI:
     * In the case of an item or section, the target must refer to an item or section in the same
     * testPart that has not yet been presented. For testParts, the target must refer to another testPart.
     *
     * @param string $identifier A QTI Identifier to be the target of the branching.
     * @throws \OutOfBoundsException If an error occurs while branching e.g. the $identifier is not referenced in the route or the target is invalid.
     * @throws \OutOfRangeException If $identifier is not a valid branching identifier.
     */
    public function branch($identifier)
    {
        try {
            $identifier = new VariableIdentifier($identifier);

            $id = ($identifier->hasPrefix() === false) ? $identifier->getVariableName() : $identifier->getPrefix();
            $occurence = ($identifier->hasPrefix() === false) ? 0 : intval($identifier->getVariableName() - 1);
        } catch (InvalidArgumentException $e) {
            $msg = "The given identifier '${identifier}' is an invalid branching target.";
            throw new OutOfRangeException($msg);
        }

        // Check for an assessmentItemRef.
        $assessmentItemRefs = $this->getAssessmentItemRefs();
        if (isset($assessmentItemRefs[$id]) === true) {

            $assessmentItemRefMap = $this->getAssessmentItemRefMap();
            $targetRouteItems = $assessmentItemRefMap[$assessmentItemRefs[$id]];

            if ($targetRouteItems[$occurence]->getTestPart() !== $this->current()->getTestPart()) {
                // From IMS QTI:
                // In case of an item or section, the target must refer to an item or section
                // in the same testPart [...]
                $msg = "Branchings to items outside of the current testPart is forbidden by the QTI 2.1 specification.";
                throw new OutOfBoundsException($msg);
            }

            $this->setPosition($this->getRouteItemPosition($targetRouteItems[$occurence]));

            return;
        }

        // Check for an assessmentSection.
        $assessmentSectionIdentifierMap = $this->getAssessmentSectionIdentifierMap();
        if (isset($assessmentSectionIdentifierMap[$id]) === true) {

            if ($assessmentSectionIdentifierMap[$id][0]->getTestPart() !== $this->current()->getTestPart()) {
                // From IMS QTI:
                // In case of an item or section, the target must refer to an item or section
                // in the same testPart [...]
                $msg = "Branchings to assessmentSections outside of the current testPart is forbidden by the QTI 2.1 specification.";
                throw new OutOfBoundsException($msg);
            }

            // We branch to the first RouteItem belonging to the section.
            $this->setPosition($this->getRouteItemPosition($assessmentSectionIdentifierMap[$id][0]));

            return;
        }

        // Check for a testPart.
        $testPartIdentifierMap = $this->getTestPartIdentifierMap();
        if (isset($testPartIdentifierMap[$id]) === true) {

            // We branch to the first RouteItem belonging to the testPart.
            if ($testPartIdentifierMap[$id][0]->getTestPart() === $this->current()->getTestPart()) {
                // From IMS QTI:
                // For testParts, the target must refer to another testPart.
                $msg = "Cannot branch to the same testPart.";
                throw new OutOfBoundsException($msg);
            }

            // We branch to the first RouteItem belonging to the testPart.
            $this->setPosition($this->getRouteItemPosition($testPartIdentifierMap[$id][0]));

            return;
        }

        // No such identifier referenced in the route, cannot branch.
        $msg = "No such identifier '${id}' found in the route for branching.";
        throw new OutOfBoundsException($msg);
    }

    /**
     * Get the position of $routeItem in the Route.
     *
     * @param \qtism\runtime\tests\RouteItem $routeItem A RouteItem you want to know the position.
     * @throws \OutOfBoundsException If no such $routeItem is referenced in the Route.
     * @return integer The position of the routeItem in the Route. The indexes begin at 0.
     */
    public function getRouteItemPosition(RouteItem $routeItem)
    {
        if (($search = array_search($routeItem, $this->getRouteItems(), true)) !== false) {
            return $search;
        } else {
            $msg = "No such RouteItem object referenced in the Route.";
            throw new OutOfBoundsException($msg);
        }
    }

    /**
     * Returns the first AssessmentItem that will be prompted if a branch targets the
     * QtiComponent set as parameter.
     *
     * This method assume that each assessmentSection and TestPart contains at least one
     * AssessmentItemRef : it then returns the first AssessmentItemRef, or the AssessmentItemRef itself.
     *
     * @param $component \qtism\data\QtiComponent The QtiComponent targeted by a branch.
     * @return \qtism\data\AssessmentItem|null The first AssessmentItem that will be prompted if a branch targets the
     * QtiComponent set as parameter.
     */
    public function getFirstItem($component)
    {
        switch ($component->getQtiClassName()) {
            case "assessmentItemRef":
                return $component;
                break;

            case "assessmentSection":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // We consider that in the runtime configuration, each section has at least one item
                return $items[0];

            case "testPart":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();

                // We consider that in the runtime configuration, each testpart has at least one item
                return $items[0];

            default:
                return null;
        }
    }

    /**
     * Returns the last AssessmentItem that will be prompted if a branch targets the
     * QtiComponent set as parameter.
     *
     * This method assume that each assessmentSection and TestPart contains at least one
     * AssessmentItemRef : it then returns the last AssessmentItemRef, or the AssessmentItemRef itself.
     *
     * @param $component \qtism\data\QtiComponent The QtiComponent targeted by a branch.
     * @return \qtism\data\AssessmentItem|null The last AssessmentItem that will be prompted if a branch targets the
     * QtiComponent set as parameter.
     */
    public function getLastItem($component)
    {
        switch ($component->getQtiClassName()) {
            case "assessmentItemRef":
                return $component;
                break;

            case "assessmentSection":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();
                return $items[count($items) - 1];

            case "testPart":
                $items = $component->getComponentsByClassName("assessmentItemRef")->getArrayCopy();
                return $items[count($items) - 1];

            default:
                return null;
        }
    }

    /**
     * Finds the new routes available with a branch. The branch must already have been analysed.
     *
     * Using the prevItem and the targetItem set as parameter, this method goes through all existing routes, to check
     * on which route a shortcut between the prevItem and the targetItem can be used : if it's the case, a new
     * route, taking the shortcut, is created.
     *
     * @param $routes array of \qtism\runtime\RouteItemCollection The list of possible routes already known
     * @param $prevItem \qtism\data\AssessmentItem the last AssessmentItem that will be prompted before the branch.
     * @param $targetItem \qtism\data\AssessmentItem the first AssessmentItem that will be prompted after the branch.
     * @param $component QtiComponent The BranchRule's QtiComponent.
     * @return array of \qtism\data\AssessmentItemRefCollection Returns the routes that can be added to the possible
     * routes due to the new possibilities afforded by the branch.
     * @throws BranchRuleTargetException if backward or recursive branching is found.
     */
    private function addRoutesWithBranches($routes, $prevItem, $targetItem, $component)
    {
        $newRoutes = [];

        if ($prevItem == $targetItem) {
            throw new BranchRuleTargetException("Recursive branching is not allowed.", BranchRuleTargetException::RECURSIVE_BRANCHING, $component);
        }

        $prevRi = null;
        $targetRi = null;

        foreach ($this->getRouteItems() as $ri) {
            if (($prevItem != null) and ($prevItem->getIdentifier() == $ri->getAssessmentItemRef()->getIdentifier())) {
                $prevRi = $ri;
            }

            if (($targetItem != null) and ($targetItem->getIdentifier() == $ri->getAssessmentItemRef()->getIdentifier())) {
                $targetRi = $ri;
            }
        }

        if ((($prevRi != null) and ($targetRi != null)) and ($this->getRouteItemPosition($prevRi) > $this->getRouteItemPosition($targetRi))) {
            throw new BranchRuleTargetException("Branching backward is not allowed.", BranchRuleTargetException::BACKWARD_BRANCHING, $component);
        }

        foreach ($routes as $route) {
            $newRoute = new RouteItemCollection();
            $routeItems = [];
            $targetRouteItem = null;
            $prevRouteItem = null;

            foreach ($route as $ritem) {
                $routeItems[] = $ritem->getAssessmentItemRef();

                if (($targetItem != null) and ($ritem->getAssessmentItemRef()->getIdentifier() == $targetItem->getIdentifier())) {
                    $targetRouteItem = $ritem;
                }

                if (($prevItem != null) and ($ritem->getAssessmentItemRef()->getIdentifier() == $prevItem->getIdentifier())) {
                    $prevRouteItem = $ritem;
                }
            }

            $deleteitems = $prevItem == null;

            if ((in_array($prevItem, $routeItems) or ($prevItem == null)) and ((in_array($targetItem, $routeItems)) or ($targetItem == null))) {

                for ($i = 0; $i < count($this->getRouteItems()); $i++) {

                    if (($targetRouteItem != null) and ($i == $this->getRouteItemPosition($targetRouteItem))) {
                        $deleteitems = false;
                    }

                    if ((!$deleteitems) and (in_array($this->getRouteItemAt($i), $route->getArrayCopy()))) {
                        $newRoute->attach($this->getRouteItemAt($i));
                    }

                    if (($prevRouteItem != null) and ($i == $this->getRouteItemPosition($prevRouteItem))) {
                        $deleteitems = true;
                    }
                }

                $newRoutes[] = $newRoute;
            }
        }

        return $newRoutes;
    }

    /**
     * Analyse the branch set as parameter and returns the list of possible routes updated with the new possibilities
     * given by the branch.
     *
     * First analyse the branch, behave appropriate if special EXIT mention, finds where the branch can create
     * shortcuts in the routes and adds these to the possible routes.
     *
     * @param $branch BranchRule The BranchRule to analyse.
     * @param $component QtiComponent The BranchRule's QtiComponent.
     * @param $routes array of \qtism\runtime\RouteItemCollection The list of possible routes already known.
     * @param $succsItem array of array of string For each AssessmentItem + the start (indexed as 0), a list of the
     * identifiers of the possible successor after the AssessmentItem. Necessary to avoid duplicating branches thus
     * routes. Argument passed by reference.
     * @param $alwaysTrueBranches array of array BranchRules The List of all BranchRules who are always true already
     * found. Argument passed by reference.
     * @param $sections AssessmentSectionCollection The collection of all sections in this AssessmentTest.
     * @param $testparts TestPartCollection The collection of all TestParts in this AssessmentTest.
     * @return array of \qtism\runtime\RouteItemCollection The list of possible routes updated with the new
     * possibilities given by the branch.
     * @throws BranchRuleTargetException if branching is recursive of backward.
     */
    private function BranchAnalysis($branch, $component, $routes, &$succsItem, &$alwaysTrueBranches, $sections, $testparts)
    {
        // Checking for invariant expressions

        $alwaysTrue = false;
        $prevItem = null;
        $targetItem = null;

        if ($branch->getExpression()->IsPure()) {

            $ee = new ExpressionEngine($branch->getExpression());

            if ($ee->process()->getValue()) {
                $alwaysTrue = true;
            } else {
                return $routes;
            }
        }

        switch ($branch->getTarget()) {

            case "EXIT_TEST":
                $prevItem = $this->getLastItem($component);

                if (!in_array(null, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = null;

                    // new successor possible => new paths possible

                    $routes = array_merge($routes, $this->addRoutesWithBranches($routes, $prevItem, null, $component));
                }
                break;
            case "EXIT_TESTPART":
                $prevItem = $this->getLastItem($component);
                $targetItem = null;
                $currentTpFound = false;

                // Find the beginning of the next testpart

                foreach ($testparts as $tp) {

                    if ($currentTpFound) {
                        $targetItem = $this->getFirstItem($tp);
                        break;
                    }

                    if ((in_array($component, $tp->getComponentsByClassName($component->getQtiClassName())->getArrayCopy()))
                        or ($component->getIdentifier() == $tp->getIdentifier())
                    ) {
                        $currentTpFound = true;
                    }
                }

                $prevItem = $this->getLastItem($component);

                if (!in_array($targetItem, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = $targetItem;
                    $routes = array_merge($routes, $this->addRoutesWithBranches($routes, $prevItem, $targetItem, $component));
                }

                break;
            case "EXIT_SECTION":

                $currentSection = null;

                if ($component->getQtiClassName() == "testPart") {
                    break;
                }

                $prevItem = $this->getLastItem($component);
                $targetItem = null;
                $prevSect = null;

                // Find the beginning of the next section

                foreach ($sections as $sect) {

                    if (($component->getQtiClassName() == "assessmentSection") and
                        ($component->getIdentifier() == $sect->getIdentifier())
                    ) {
                        $prevSect = $sect;
                        break;
                    } else {
                        if ((($component->getQtiClassName() != "assessmentSection")) and
                            (in_array($component, $sect->getComponentsByClassName($component->getQtiClassName())->getArrayCopy()))
                        ) {
                            $prevSect = $sect;
                            // No break to be sure that the deepest section is taken
                        }
                    }
                }

                $currentSctFound = false;

                foreach ($sections as $sect) {

                    if ($currentSctFound and (!in_array($sect, $prevSect->getSectionParts()->getArrayCopy()))) {
                        $targetItem = $this->getFirstItem($sect);
                        break;
                    }

                    if ($sect->getIdentifier() == $prevSect->getIdentifier()) {
                        $currentSctFound = true;
                    }
                }

                if (!in_array($targetItem, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = $targetItem;
                    $routes = array_merge($routes, $this->addRoutesWithBranches($routes, $prevItem, $targetItem, $component));
                }

                break;

            default:
                $prevItem = $this->getLastItem($component);
                $targetItem = null;
                $found = false;

                foreach ($testparts as $tp) {
                    if ($tp->getIdentifier() == $branch->getTarget()) {
                        $targetItem = $this->getRouteItemsByTestPart($branch->getTarget())[0]->getAssessmentItemRef();
                        $found = true;
                    }
                }

                if (!$found) {
                    foreach ($sections as $section) {
                        if ($section->getIdentifier() == $branch->getTarget()) {
                            $targetItem = $this->getRouteItemsByAssessmentSection($branch->getTarget())[0]->getAssessmentItemRef();
                            $found = true;
                        }
                    }
                }

                if (!$found) {
                    try {
                        $targetItem = $this->getRouteItemsByAssessmentItemRef($branch->getTarget())[0]->getAssessmentItemRef();
                    } catch (OutOfBoundsException $ex) {
                        throw new BranchRuleTargetException("Target '" . $branch->getTarget() .
                            "' doesn't exist.", BranchRuleTargetException::UNKNOWN_TARGET, $tp);
                    }
                }

                if (!in_array($targetItem, $succsItem[$prevItem->getIdentifier()])) {
                    $succsItem[$prevItem->getIdentifier()][] = $targetItem;
                    $routes = array_merge($routes, $this->addRoutesWithBranches($routes, $prevItem, $targetItem, $component));
                }

                break;
        }

        if ($alwaysTrue) {
            $alwaysTrueBranches[] = [$branch, $prevItem, $targetItem];
        }

        return $routes;
    }

    /**
     * Checks if a PreCondition makes it's owner never reachable.
     *
     * Loops through all preconditions : if they're all pure expression that are always false,
     * returns true, otherwise return false.
     *
     * @param $preconditions qtism\data\rules\PreConditionCollection The PreConditions to check.
     * @return bool True if the PreCondition is always false, false otherwise
     */

    public static function checkIfAlwaysFalse($preconditions) {

        foreach ($preconditions as $pre) {

            if ($pre->getExpression()->IsPure()) {

                $ee = new ExpressionEngine($pre->getExpression());

                if ($ee->process()->getValue()) {
                    // Always true, so never false
                    return false;
                }
            } else { // Variable, so not always false
                return false;
            }
        }

        return true;
    }

    /**
     * @param $preconditions qtism\data\rules\PreConditionCollection The PreConditions to check.
     * @param $routes array of \qtism\runtime\RouteItemCollection The list of possible routes already known.
     * @param $items array of qtism\data\AssessmentItemRef The items that are skipped if the precondition is false.
     * @return array of \qtism\runtime\RouteItemCollection The list of possible routes updated with the new
     * possibilities (or not) given by the precondition.
     */

    public function analysePreConditions($preconditions, $routes, $items) {

        $alwaysFalse = true;

        foreach($preconditions as $pre) {

            if ($pre->getExpression()->IsPure()) {

                $ee = new ExpressionEngine($pre->getExpression());

                if ($ee->process()->getValue()) {
                    // Always true, so nothing changes
                    return $routes;

                }
            } else {
                $alwaysFalse = false;
            }
        }

        if ($alwaysFalse) {

            // Removing each instance of the never reached AssessmentItemRef

            for ($h = 0; $h < count($routes); $h++) {

                $routeitems = new AssessmentItemRefCollection();

                foreach ($routes[$h] as $routeitem) {
                    $routeitems->attach($routeitem->getAssessmentItemRef());
                }

                if (!array_diff($items, $routeitems->getArrayCopy())) {

                    $newRoute = new RouteItemCollection($routes[$h]->getArrayCopy());
                    $routes[$h] = new RouteItemCollection();

                    for ($i = 0; $i < count($newRoute); $i++) {

                        if (in_array($newRoute[$i]->getAssessmentItemRef(), $items)) {
                            $newRoute->remove($newRoute[$i]);
                        }
                    }

                    foreach ($newRoute as $routeitem) {
                        $routes[$h][] = $routeitem;
                    }
                }
            }

            // Removing the item(s) in each route where they are

        } else {
            foreach ($routes as $route) {

                $routeitems = new AssessmentItemRefCollection();

                foreach ($route as $routeitem) {
                    $routeitems->attach($routeitem->getAssessmentItemRef());
                }

                if (!array_diff($items, $routeitems->getArrayCopy())) {
                    $newRoute = new RouteItemCollection();

                    foreach ($routeitems as $item) {

                        if (!in_array($item, $items)) {
                            $newRoute->attach($this->getRouteItemsByAssessmentItemRef($item->getIdentifier())[0]);
                        }
                    }

                    if (($newRoute != null) and (!in_array($newRoute, $routes))) {
                        $routes[] = $newRoute;
                    }
                }
            }
        }

        return $routes;
    }

    /**
     * Returns an array with all possible routes for an AssessmentTest.
     *
     * Create the list with all possible routes that a candidate can take through this Route.
     * It creates new shorter routes, that can been taken with branches targeting further forward.
     * Then it creates the new routes possible with items that are not mandatory due to the precondition.
     *
     * @param Boolean $asArray true to return an array, false to return an \qtism\runtime\RouteItemCollection.
     * @return array of array of \qtism\runtime\RouteItem | array of \qtism\runtime\RouteItemCollection
     * @throws BranchRuleTargetException if branching is recursive of backward.
     */
    public function getPossibleRoutes($asArray = false)
    {
        $routes = [];
        $routes[] = new RouteItemCollection($this->getRouteItems());

        $testparts = new TestPartCollection();
        $sections = new AssessmentSectionCollection();
        $alwaysTrueBranches = [];
        $unreachableItems = [];

        foreach ($this->getRouteItems() as $ri) {

            if (!in_array($ri->getTestPart(), $testparts->getArrayCopy())) {
                $testparts[] = $ri->getTestPart();
            }

            foreach ($ri->getAssessmentSections() as $section) {
                if (!in_array($section, $sections->getArrayCopy())) {
                    $sections[] = $section;
                }
            }
        }

        // Array associating to each item the possible successor item, the same for the sections and parts

        $succsItem = [];
        $succsItem[0] = [];

        // Association of the successor item to the next one

        for ($i = 0; $i < count($this->getRouteItems()); $i++) {
            $succsItem[$this->getRouteItemAt($i)->getAssessmentItemRef()->getIdentifier()] = [];

            if ($i < (count($this->getRouteItems()) - 1)) {
                $succsItem[$this->getRouteItemAt($i)->getAssessmentItemRef()->getIdentifier()][] = $this->getRouteItemAt($i + 1)->getAssessmentItemRef();
            } else {
                $succsItem[$this->getRouteItemAt($i)->getAssessmentItemRef()->getIdentifier()][] = null;
            }
        }

        // First, finding if there are always false preconditions, from which branches must not be considered

        $preTp = [];
        $preSect = [];

        foreach ($this->getRouteItems() as $ri) {

            if (count($ri->getPreConditions()) > 0) {

                $preCount = count($ri->getPreConditions());

                // Handling testparts

                if (count($ri->getTestPart()->getPreConditions()) > 0) {

                    $preCount -= count($ri->getTestPart()->getPreConditions());

                    if (!in_array($ri->getTestPart(), $preTp)) {

                        $preTp[] = $ri->getTestPart();

                        if (Route::checkIfAlwaysFalse($ri->getTestPart()->getPreConditions())) {
                            foreach ($ri->getTestPart()->getComponentsByClassName("assessmentItemRef") as $item) {
                                if (!in_array($item, $unreachableItems)) {
                                    $unreachableItems[] = $item;
                                }
                            }
                        }
                    }
                }

                // Handling sections

                $sect = $ri->getAssessmentSections();

                foreach ($sect as $section) {
                    if (count($section->getPreConditions()) > 0) {

                        $preCount -= count($section->getPreConditions());

                        if (!in_array($section, $preSect)) {

                            $preSect[] = $section;

                            if (Route::checkIfAlwaysFalse($section->getPreConditions())) {
                                foreach ($section->getComponentsByClassName("assessmentItemRef") as $item) {
                                    if (!in_array($item, $unreachableItems)) {
                                        $unreachableItems[] = $item;
                                    }
                                }
                            }
                        }
                    }
                }

                // Handling assessmentItem

                if ($preCount > 0) {

                    $preConditions = new PreConditionCollection();

                    for ($i = 0; $i < $preCount; $i++) {
                        $preConditions[] = $ri->getPreConditions()[$i];
                    }

                    if (Route::checkIfAlwaysFalse($preConditions)) {
                        if (!in_array($ri->getAssessmentItemRef(), $unreachableItems)) {
                            $unreachableItems[] = $ri->getAssessmentItemRef();
                        }
                    }
                }
            }
        }

        // Checking existing branches to add other possible previous items

        $branchTp = [];
        $branchSect = [];

        foreach ($this->getRouteItems() as $ri) {

            if (!in_array($ri->getAssessmentItemRef(), $unreachableItems)) {
                // Handling testparts

                $tp = $ri->getTestPart();

                if (!in_array($tp, $branchTp)) {
                    if (count($tp->getBranchRules()) > 0) {
                        foreach ($tp->getBranchRules() as $branch) {
                            $routes = $this->BranchAnalysis($branch, $tp, $routes, $succsItem, $alwaysTrueBranches,
                                $sections, $testparts);

                            // Avoid next branches if an AlwaysTrue branch has been found

                            if ((count($alwaysTrueBranches) > 0) and
                                ($alwaysTrueBranches[count($alwaysTrueBranches) - 1][0] == $branch)
                            ) {
                                break;
                            }
                        }

                        $branchTp[] = $tp;
                    }
                }

                // Handling sections

                $sects = $ri->getAssessmentSections();

                foreach ($sects as $section) {
                    if (!in_array($section, $branchSect)) {
                        if (count($section->getBranchRules()) > 0) {
                            foreach ($section->getBranchRules() as $branch) {
                                $routes = $this->BranchAnalysis($branch, $section, $routes, $succsItem, $alwaysTrueBranches,
                                    $sections, $testparts);

                                // Avoid next branches if an AlwaysTrue branch has been found

                                if ((count($alwaysTrueBranches) > 0) and
                                    ($alwaysTrueBranches[count($alwaysTrueBranches) - 1][0] == $branch)
                                ) {
                                    break;
                                }
                            }

                            $branchSect[] = $section;
                        }
                    }
                }

                // Handling assessmentItem

                if (count($ri->getBranchRules()) > 0) {
                    foreach ($ri->getBranchRules() as $branch) {
                        $routes = $this->BranchAnalysis($branch, $ri->getAssessmentItemRef(), $routes, $succsItem,
                            $alwaysTrueBranches, $sections, $testparts);

                        // Avoid next branches if an AlwaysTrue branch has been found

                        if ((count($alwaysTrueBranches) > 0) and
                            ($alwaysTrueBranches[count($alwaysTrueBranches) - 1][0] == $branch)
                        ) {
                            break;
                        }
                    }
                }
            }
        }

        // Removing paths due to always true branches

        foreach ($alwaysTrueBranches as $branchData) {

            for ($i = 0; $i < count($routes); $i++) {

                $removeRoute = false;
                $prevItemFound = false;

                foreach ($routes[$i] as $routeItem) {

                    if ($routeItem->getAssessmentItemRef()->getIdentifier() == $branchData[1]->getIdentifier()) {
                        $prevItemFound = true;
                    }
                    else if (($branchData[2] != null) and
                        ($routeItem->getAssessmentItemRef()->getIdentifier() == $branchData[2]->getIdentifier())) {
                        $prevItemFound = false;
                    }
                    else if ($prevItemFound) {
                        $removeRoute = true;
                        break;
                    }
                }

                if ($removeRoute) {

                    unset($routes[$i]);
                    $routes = array_values($routes);
                    $i--;
                }
            }
        }

        // Checking preConditions in tests, sections and items

        $preTp = [];
        $preSect = [];

        foreach ($this->getRouteItems() as $ri) {

            if (count($ri->getPreConditions()) > 0) {

                $preCount = count($ri->getPreConditions());

                // Handling testparts

                if (count($ri->getTestPart()->getPreConditions()) > 0) {

                    $preCount -= count($ri->getTestPart()->getPreConditions());

                    if (!in_array($ri->getTestPart(), $preTp)) {

                        $preTp[] = $ri->getTestPart();
                        $tpItems = $ri->getTestPart()->getComponentsByClassName("assessmentItemRef")->getArrayCopy();
                        $routes = $this->analysePreConditions($ri->getTestPart()->getPreConditions(), $routes, $tpItems);
                    }
                }

                // Handling sections

                $sect = $ri->getAssessmentSections();

                foreach ($sect as $section) {
                    if (count($section->getPreConditions()) > 0) {

                        $preCount -= count($section->getPreConditions());

                        if (!in_array($section, $preSect)) {
                            $preSect[] = $section;
                            $sectItems = $section->getComponentsByClassName("assessmentItemRef")->getArrayCopy();
                            $routes = $this->analysePreConditions($section->getPreConditions(), $routes, $sectItems);
                        }
                    }
                }
                // Handling assessmentItem

                if ($preCount > 0) {

                    $preConditions = new PreConditionCollection();

                    for ($i = 0; $i < $preCount; $i++) {
                        $preConditions[] = $ri->getPreConditions()[$i];
                    }

                    $routes = $this->analysePreConditions($preConditions, $routes, [$ri->getAssessmentItemRef()]);
                }
            }
        }

        // Transform into array if necessary

        if ($asArray) {
            foreach ($routes as $key => $route) {
                $routes[$key] = $route->getArrayCopy();
            }
        }

        return $routes;
    }

    /**
     * Returns an array with all possible routes for this Route,
     * starting from the current position of the test taker.
     *
     * Create the list with all possible routes that a candidate can take through this Route,
     * starting from the current position of the test taker.
     * With the possible routes for the whole test given as parameter, it iterates on each RouteItem of each Route
     * of the possible routes; when it finds the same RouteItem as the current position of the test taker,
     * it copies the rest of that Route and adds it to the collection of possible Routes.
     *
     * @param $routes array of \qtism\runtime\RouteItemCollection The collection of all the possible Routes
     * than can be taken to complete the AssessmentTest from the start to the end.
     * @return array of array of \qtism\runtime\RouteItem | array of \qtism\runtime\RouteItemCollection
     */
    public function getPossibleRoutesFromCurrentPosition($routes)
    {
        $curItem = $this->getRouteItemAt($this->getPosition());
        $routesFCP = [];

        foreach ($routes as $route) {

            $newRoute = new RouteItemCollection();
            $curItemFound = false;

            foreach ($route as $routeItem) {

                if ($curItem->getAssessmentItemRef()->getIdentifier() ==
                    $routeItem->getAssessmentItemRef()->getIdentifier()
                ) {
                    $curItemFound = true;
                }

                if ($curItemFound) {
                    $newRoute[] = $routeItem;
                }
            }

            if ((count($newRoute) > 0) and (!in_array($newRoute, $routesFCP))) {
                $routesFCP[] = $newRoute;
            }
        }

        return $routesFCP;
    }

    /**
     * Returns an array with all shortest possible routes for this Route.
     *
     * Iterates on all possible routes and when it finds a route shorter than the minimum length,
     * it is stored as the new shortest route.
     *
     * @return array of qtism\runtime\Route An array with all shortest possible routes
     * for this Route.
     */
    public function getShortestRoutes()
    {
        $routes = $this->getPossibleRoutes(false);
        $minCount = PHP_INT_MAX;
        $minRoutes = [];

        foreach ($routes as $route) {
            if (sizeof($route) < $minCount) {
                $minCount = sizeof($route);
                $minRoutes = [];
            }

            if (sizeof($route) <= $minCount) {
                $minRoutes[] = $route;
            }
        }

        return $minRoutes;
    }

    /**
     * Returns an array with all shortest possible routes for this Route.
     *
     * Iterates on all possible routes and when it finds a route shorter than the minimum length,
     * it is stored as the new shortest route.
     *
     * @return array of qtism\runtime\Route An array with all shortest possible routes
     * for this Route.
     */
    public function getShortestRoutesFromCurrentPosition($routes)
    {
        $routesFCP = $this->getPossibleRoutesFromCurrentPosition($routes);
        $minCount = PHP_INT_MAX;
        $minRoutes = [];

        foreach ($routesFCP as $route) {
            if (sizeof($route) < $minCount) {
                $minCount = sizeof($route);
                $minRoutes = [];
            }

            if (sizeof($route) <= $minCount) {
                $minRoutes[] = $route;
            }
        }

        return $minRoutes;
    }

    /**
     * Returns an array with all longest possible routes for this Route,
     * starting from the current position of the test taker.
     * Currently it's the route with all items that will always be returned.
     *
     * Iterates on all possible routes starting from the current position of the test taker
     * and when it finds a route longer than the maximum length,
     * it is stored as the new longest route.
     *
     * @return array of qtism\runtime\Route An array with all longest possible routes
     * for this Route.
     */
    public function getLongestRoutes()
    {
        $routes = $this->getPossibleRoutes(false);
        $maxCount = 0;
        $maxRoutes = [];

        foreach ($routes as $route) {
            if (sizeof($route) > $maxCount) {
                $maxCount = sizeof($route);
                $maxRoutes = [];
            }

            if (sizeof($route) >= $maxCount) {
                $maxRoutes[] = $route;
            }
        }

        return $maxRoutes;
    }

    /**
     * Returns an array with all longest possible routes for this Route,
     * starting from the current position of the test taker.
     * Currently it's the route with all remaining items that will always be returned.
     *
     * Iterates on all possible routes starting from the current position of the test taker
     * and when it finds a route longer than the maximum length,
     * it is stored as the new longest route.
     *
     * @return array of qtism\runtime\Route An array with all longest possible routes
     * for this Route.
     */
    public function getLongestRoutesFromCurrentPosition($routes)
    {
        $routesFCP = $this->getPossibleRoutesFromCurrentPosition($routes);
        $maxCount = 0;
        $maxRoutes = [];

        foreach ($routesFCP as $route) {
            if (sizeof($route) > $maxCount) {
                $maxCount = sizeof($route);
                $maxRoutes = [];
            }

            if (sizeof($route) >= $maxCount) {
                $maxRoutes[] = $route;
            }
        }

        return $maxRoutes;
    }
}
