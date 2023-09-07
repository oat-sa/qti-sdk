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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\runtime\storage\common;

use OutOfBoundsException;
use qtism\data\AssessmentTest;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentIterator;

/**
 * The AssessmentTestSeeker enables you to search for QTIComponent objects
 * by position in the tree formed by an AssessmentTest object.
 */
class AssessmentTestSeeker
{
    /**
     * An array that counts the number of explored components
     * by QTI class name.
     *
     * @var array
     */
    private $classCounter;

    /**
     * An array that stores the already explored components by QTI class name.
     *
     * @var array
     */
    private $componentStore;

    /**
     * A QtiComponent iterator.
     *
     * @var QtiComponentIterator
     */
    private $iterator;

    /**
     * Create an AssessmentTestSeeker object that will be able to search
     * for QTIComponent objects in an AssessmentTest that matches the QTI class
     * names described by $classes.
     *
     * @param AssessmentTest $test An AssessmentTest object.
     * @param array $classes An array of strings that are QTI class names to be seeked. An empty array means all the QTI classes are seekable.
     */
    public function __construct(AssessmentTest $test, array $classes)
    {
        $this->setIterator(new QtiComponentIterator($test, $classes));
        $this->setClassCounter([]);
        $this->setComponentStore([]);
    }

    /**
     * Set the array that counts the number of explored components by QTI class names.
     *
     * @param array $classCounter An array where keys are QTI class names and values are the count of explored components for this class name.
     */
    protected function setClassCounter(array $classCounter): void
    {
        $this->classCounter = $classCounter;
    }

    /**
     * Get a reference on the array that counts the number of explored components by QTI class names.
     *
     * @return array An array where keys are QTI class names and values are the count of explored components for this class name.
     */
    protected function &getClassCounter(): array
    {
        return $this->classCounter;
    }

    /**
     * Get the QtiComponentIterator that explores the test.
     *
     * @param QtiComponentIterator $iterator
     */
    protected function setIterator(QtiComponentIterator $iterator): void
    {
        $this->iterator = $iterator;
    }

    /**
     * Set the QtiComponentIterator that explores the test.
     *
     * @return QtiComponentIterator
     */
    protected function getIterator(): QtiComponentIterator
    {
        return $this->iterator;
    }

    /**
     * Set the componentStore array where keys are QTI class names
     * and value are arrays where the keys are the positions of the already
     * explored components and the values are the components themselves.
     *
     * @param array $componentStore
     */
    protected function setComponentStore(array $componentStore): void
    {
        $this->componentStore = $componentStore;
    }

    /**
     * Get the componentStore array where keys are QTI class names
     * and value are arrays where the keys are the positions of the already
     * explored components and the values are the components themselves.
     *
     * @return array
     */
    protected function &getComponentStore(): array
    {
        return $this->componentStore;
    }

    /**
     * Add the component in the ComponentStore.
     *
     * @param QtiComponent $component A QTI Component.
     * @return int The position in the AssessmentTest tree the component was found.
     */
    protected function addToComponentStore(QtiComponent $component): int
    {
        $class = $component->getQtiClassName();

        if (isset($this->componentStore[$class]) === false) {
            $this->componentStore[$class] = [];
        }

        $position = $this->getClassCount($class);
        $this->componentStore[$class][$position] = $component;
        $this->incrementClassCount($component);

        return $position;
    }

    /**
     * Get a QtiComponent object from the component store that has $class for QTI class name
     * and which is at position $position in the AssessmentTest tree.
     *
     * @param string $class A QTI class name.
     * @param int $position A position in the AssessmentTest tree.
     * @return bool|QtiComponent A QtiComponent object or false if it is not found.
     */
    protected function getComponentFromComponentStore($class, $position)
    {
        $component = false;

        if (isset($this->componentStore[$class]) === true && isset($this->componentStore[$class][$position]) === true) {
            $component = $this->componentStore[$class][$position];
        }

        return $component;
    }

    /**
     * Get the position in the AssessmentTest tree for $component.
     *
     * @param QtiComponent $component
     * @return false|int The position of $component in the AssessmentTest tree ir false if it could not be found.
     */
    protected function getPositionFromComponentStore(QtiComponent $component)
    {
        $position = false;
        $class = $component->getQtiClassName();

        if ((isset($this->componentStore[$class]) === true) && ($search = array_search($component, $this->componentStore[$class], true)) !== false) {
            $position = $search;
        }

        return $position;
    }

    /**
     * Seek for a QtiComponent object that has $class for QTI class name
     * and that is in position $position in the AssessmentTest tree.
     *
     * @param string $class A QTI class name.
     * @param int $position A position in the AssessmentTest tree.
     * @return QtiComponent The QtiComponent object that corresponds to $class and $position.
     * @throws OutOfBoundsException If no such QtiComponent could be found in the AssessmentTest tree.
     */
    public function seekComponent($class, $position): QtiComponent
    {
        if (($component = $this->getComponentFromComponentStore($class, $position)) !== false) {
            // Already explored!
            return $component;
        }

        // Not already explored.
        // Continue to iterate until its found.
        while ($this->iterator->valid() === true) {
            $component = $this->iterator->current();
            $newPosition = $this->addToComponentStore($component);

            $this->iterator->next();

            if ($class === $component->getQtiClassName() && $newPosition === $position) {
                return $component;
            }
        }

        $msg = "Unable to find a QtiComponent object with QTI class '{$class}' at position '{$position}'.";
        throw new OutOfBoundsException($msg);
    }

    /**
     * Seek for the position of $component in the AssessmentTest tree.
     *
     * @param QtiComponent $component A QtiComponent object which is supposed to be in the AssessmentTest tree.
     * @return int The position of $component in the AssessmentTest tree.
     * @throws OutOfBoundsException If no such $component could be found in the AssessmentTest tree.
     */
    public function seekPosition(QtiComponent $component): int
    {
        if (($position = $this->getPositionFromComponentStore($component)) !== false) {
            // Already explored.
            return $position;
        }

        // We have to find it!
        while ($this->iterator->valid() === true) {
            $current = $this->iterator->current();
            $newPosition = $this->addToComponentStore($current);

            $this->iterator->next();

            if ($current === $component) {
                return $newPosition;
            }
        }

        $class = $component->getQtiClassName();
        $msg = "Unable to find the position of a QtiComponent with QTI class '{$class}'.";
        throw new OutOfBoundsException($msg);
    }

    /**
     * Increment the number of explored components by class name thanks
     * to a given $component.
     *
     * @param QtiComponent $component A QtiComponent object.
     */
    protected function incrementClassCount(QtiComponent $component): void
    {
        $class = $component->getQtiClassName();

        if (isset($this->classCounter[$class]) === false) {
            $this->classCounter[$class] = 0;
        }

        $this->classCounter[$class]++;
    }

    /**
     * Get the number of explored components for a given QTI $class name.
     *
     * @param string $class A QTI class name.
     * @return int The number of explored components that belong to the $class.
     */
    protected function getClassCount($class): int
    {
        $count = 0;

        if (isset($this->classCounter[$class]) === true) {
            $count = $this->classCounter[$class];
        }

        return $count;
    }

    /**
     * Get the AssessmentTest object on which the AssessmentTestSeeker
     * is iterating.
     *
     * @return AssessmentTest An AssessmentTest object.
     */
    public function getAssessmentTest(): AssessmentTest
    {
        return $this->getIterator()->getRootComponent();
    }
}
