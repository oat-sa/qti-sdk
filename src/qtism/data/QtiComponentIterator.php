<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data;

use \Iterator;

/**
 * An Iterator that makes you able to loop on the QtiComponent objects
 * contained by a given QtiComponent object.
 *
 * The following example demonstrates how QtiComponentIterator works:
 *
 * <code>
 * $baseValues = new ExpressionCollection();
 * $baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
 * $baseValues[] = new BaseValue(BaseType::INTEGER, 25);
 * $baseValues[] = new BaseValue(BaseType::FLOAT, 0.5);
 *
 * // Let's iterate on the components containted by a Sum object.
 * $iterator = new QtiComponentIterator(new Sum($baseValues));

 * $iterations = 0;
 * foreach ($iterator as $k => $i) {
 *    // $k contains the QTI class name of the component.
 *    // $i contains a reference to the component objec.
 *    var_dump($k, $i);
 * }
 *
 * // Output is...
 * // string(9) "baseValue"
 * // float(0.5)
 * // string(9) "baseValue"
 * // int(25)
 * // string(9) "baseValue"
 * // float(0.5)
 * </code>
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class QtiComponentIterator implements Iterator
{
    /**
	 * The QtiComponent object which contains the QtiComponent objects
	 * to be traversed.
	 *
	 * @var \qtism\data\QtiComponent
	 */
    private $rootComponent = null;

    /**
	 * The QtiComponent object being traversed.
	 *
	 * @var \qtism\data\QtiComponent
	 */
    private $currentComponent = null;

    /**
	 * Whether the iterator state is valid.
	 *
	 * @var boolean
	 */
    private $isValid = true;

    /**
	 * A stack containing the QtiComponents to be traversed.
	 *
	 * Each value in the trail is an array where:
	 * * index [0] contains the source of the trailing phase
	 * * index [1] contains the next QtiComponent object to traverse.
	 *
	 * @var array
	 */
    private $trail = array();

    /**
	 * An array of already traversed QtiComponent objects.
	 *
	 * @var array
	 */
    private $traversed = array();

    /**
	 * The QtiComponent object which is the container of the QtiComponent object
	 * returned by QtiComponentIterator::current().
	 *
	 * @var \qtism\data\QtiComponent
	 */
    private $currentContainer = null;

    /**
	 * The QTI classes the Iterator must take into account.
	 *
	 * @var array
	 */
    private $classes;

    /**
	 * The number of occurences in the trail.
	 *
	 * @var integer
	 */
    private $trailCount = 0;

    /**
	 * Create a new QtiComponentIterator object.
	 *
	 * @param \qtism\data\QtiComponent $rootComponent The QtiComponent which contains the QtiComponent objects to be traversed.
	 */
    public function __construct(QtiComponent $rootComponent, array $classes = array())
    {
        $this->setRootComponent($rootComponent);
        $this->setClasses($classes);
        $this->rewind();
    }

    /**
	 * Set the root QtiComponent. In other words, the QtiComponent which
	 * contains the QtiComponent objects to be traversed.
	 *
	 * @param \qtism\data\QtiComponent $component
	 */
    protected function setRootComponent(QtiComponent $rootComponent)
    {
        $this->rootComponent = $rootComponent;
    }

    protected function setCurrentContainer(QtiComponent $currentContainer = null)
    {
        $this->currentContainer = $currentContainer;
    }

    public function getCurrentContainer()
    {
        return $this->currentContainer;
    }

    /**
	 * Get the root QtiComponent. In other words, the QtiComponent which contains
	 * the QtiComponent objects to be traversed.
	 *
	 * @return \qtism\data\QtiComponent
	 */
    public function getRootComponent()
    {
        return $this->rootComponent;
    }

    /**
	 * Set the currently traversed QtiComponent object.
	 *
	 * @param \qtism\data\QtiComponent $currentComponent
	 */
    protected function setCurrentComponent(QtiComponent $currentComponent = null)
    {
        $this->currentComponent = $currentComponent;
    }

    /**
	 * Get the currently traversed QtiComponent object.
	 *
	 * @return \qtism\data\QtiComponent A QtiComponent object.
	 */
    protected function getCurrentComponent()
    {
        return $this->currentComponent;
    }

    /**
	 * Set the QTI classes the Iterator must take into account.
	 *
	 * @param array $classes An array of QTI class names.
	 */
    protected function setClasses(array $classes)
    {
        $this->classes = $classes;
    }

    /**
	 * Get the QTI classes the Iterator must take into account.
	 *
	 * @return array An array of QTI class names.
	 */
    protected function &getClasses() 
    {
        return $this->classes;
    }
    
    protected function getTrailCount()
    {
        return $this->trailCount;
    }
    
    protected function setTrailCount($trailCount)
    {
        $this->trailCount = $trailCount;
    }
    
    protected function incrementTrailCount()
    {
        $trailCount = $this->getTrailCount() + 1;
        $this->setTrailCount($trailCount);
    }
    
    protected function decrementTrailCount()
    {
        $trailCount = $this->getTrailCount() - 1;
        $this->setTrailCount($trailCount);
    }

    /**
	 * Push a trail entry on the trail.
	 *
	 * @param \qtism\data\QTIComponent $source From where we are coming from.
	 * @param \qtism\data\QTIComponentCollection $components The next components to explore.
	 */
    protected function pushOnTrail(QtiComponent $source, QtiComponentCollection $components)
    {     
        foreach (array_reverse($components->getArrayCopy()) as $c) {
            array_push($this->trail, array($source, $c));
            $this->trailCount++;
        }
    }

    /**
	 * Pop a trail entry from the trail.
	 *
	 * @return array
	 */
    protected function popFromTrail()
    {
        $this->trailCount--;
        return array_pop($this->trail);
    }

    /**
	 * Get a reference on the trail array.
	 *
	 * @return array An array of QtiComponent objects.
	 */
    protected function &getTrail() 
    {
        return $this->trail;
    }

    /**
	 * Set the trail array.
	 *
	 * @param array $trail An array of QtiComponent objects.
	 */
    protected function setTrail(array &$trail)
    {
        $this->trail = $trail;
        $this->trailCount = count($trail);
    }

    /**
	 * Set the array of QtiComponents which contains the already traversed
	 * components.
	 *
	 * @param array $traversed An array of QtiComponent objects.
	 */
    protected function setTraversed(array &$traversed)
    {
        $this->traversed = $traversed;
    }

    /**
	 * Get a reference on the array of QtiComponents which contains the already
	 * traversed components.
	 *
	 * @return array An array of QtiComponent objects.
	 */
    protected function &getTraversed() 
    {
        return $this->traversed;
    }

    /**
	 * Mark a QTIComponent object as traversed.
	 *
	 * @param \qtism\data\QtiComponent $component A QTIComponent object.
	 */
    protected function markTraversed(QtiComponent $component)
    {
        array_push($this->traversed, $component);
    }

    /**
	 * Whether or not a given $component has already been traversed by
	 * the iterator.
	 *
	 * @param \qtism\data\QtiComponent $component
	 */
    protected function isTraversed(QtiComponent $component)
    {
        return in_array($component, $this->traversed, true);
    }

    /**
	 * Indicate Whether the iterator is still valid.
	 *
	 * @param boolean $isValid
	 */
    protected function setValid($isValid)
    {
        $this->isValid = $isValid;
    }

    /**
	 * Rewind the iterator.
	 */
    public function rewind()
    {
        $trail = array();
        $this->setTrail($trail);
        $classes = &$this->getClasses();

        $traversed = array();
        $this->setTraversed($traversed);

        $root = $this->getRootComponent();
        $this->pushOnTrail($root, $root->getComponents());

        $hasTrail = false;
        while (count($this->getTrail()) > 0) {
            $hasTrail = true;
            $trailEntry = $this->popFromTrail();

            $this->setValid(true);
            $this->currentComponent = $trailEntry[1];
            $this->currentContainer = $trailEntry[0];
            $this->markTraversed($this->currentComponent);
            $this->pushOnTrail($this->currentComponent, $this->currentComponent->getComponents());

            if (empty($classes) === true || in_array($this->currentComponent->getQtiClassName(), $classes) === true) {
                break;
            }
        }

        if (count($this->trail) === 0 && !$hasTrail) {
            $this->isValid = false;
            $this->currentComponent = null;
            $this->currentContainer = null;
        }
    }

    /**
	 * Get the current QtiComponent object the iterator
	 * is traversing.
	 *
	 * @return \qtism\data\QtiComponent A QtiComponent object.
	 */
    public function current()
    {
        return $this->currentComponent;
    }

    /**
	 * Get the parent component of the one given by
	 * the QtiComponentIterator::current() method.
	 *
	 * This method will return the null value in the following circumstances:
	 *
	 * * The QtiComponentIterator::valid method returns false.
	 * * The component returned by QtiComponentIterator::current is the root component.
	 *
	 * @return null|\qtism\data\QtiComponent The null value if there is no parent, otherwise a QtiComponent.
	 * @see \qtism\data\QtiComponentIterator::current()
	 */
    public function parent()
    {
        return $this->currentContainer;
    }

    /**
	 * Get the key of the current QtiComponent. The value of the key is actually
	 * its QTI class name e.g. 'assessmentTest', 'assessmentItemRef', ...
	 *
	 * @return string A QTI class name.
	 */
    public function key()
    {
        return $this->currentComponent->getQtiClassName();
    }

    /**
	 * Moves the current position to the next QtiComponent object to be
	 * traversed.
	 */
    public function next()
    {
        if ($this->trailCount > 0) {

            while ($this->trailCount > 0) {
                $trailEntry = $this->popFromTrail();
                $component = $trailEntry[1];
                $source = $trailEntry[0];

                if ($this->isTraversed($component) === false) {
                    $this->currentComponent = $component;
                    $this->currentContainer = $source;
                    $this->pushOnTrail($component, $this->currentComponent->getComponents());
                    $this->markTraversed($this->currentComponent);

                    if (empty($this->classes) === true || in_array($this->currentComponent->getQTIClassName(), $this->classes) === true) {
                        // If all classes are seeked or the current component has a class name
                        // that must be seeked, stop the iteration.
                        return;
                    }
                }
            }

            $this->isValid = false;
            $this->currentContainer = null;
        } else {
            $this->isValid = false;
            $this->currentContainer = null;
        }
    }

    /**
	 * Checks if current position is valid.
	 *
	 * @return boolean Whether the current position is valid.
	 */
    public function valid()
    {
        return $this->isValid;
    }
}
