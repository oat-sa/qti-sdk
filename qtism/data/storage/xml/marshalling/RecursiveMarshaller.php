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
 * Copyright (c) 2013-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use DOMNode;
use DOMText;
use qtism\common\collections\AbstractCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * An abstract recursive implementation of Marshaller. By "recursive" we mean
 * a Marshaller which works with an access to already marshalled/unmarshalled
 * children QTI components/DOM elements.
 */
abstract class RecursiveMarshaller extends Marshaller
{
    /**
     * The trail stack
     *
     * @var array
     */
    private $trail = [];

    /**
     * The marker list.
     *
     * @var array
     */
    private $mark = [];

    /**
     * The stack of final unmarshalled/marshalled objects.
     *
     * @var array
     */
    private $final = [];

    /**
     * The list of processed objects.
     *
     * @var array
     */
    private $processed = [];

    /**
     * Push an object on the processed objects list.
     *
     * @param mixed $object An object value.
     */
    protected function pushProcessed($object)
    {
        array_push($this->processed, $object);
    }

    /**
     * Get the list of objects processed so far.
     *
     * @return array An array of object values.
     */
    protected function getProcessed()
    {
        return $this->processed;
    }

    /**
     * Reset the list of objects processed so far.
     */
    protected function resetProcessed()
    {
        $this->processed = [];
    }

    /**
     * Push an object on the trail stack.
     *
     * @param mixed $object An object to push on the trail stack.
     */
    protected function pushTrail($object)
    {
        array_push($this->trail, $object);
    }

    /**
     * Pop an object from the trail stack.
     *
     * @return mixed An object popped from the trail stack.
     */
    protected function popTrail()
    {
        return array_pop($this->trail);
    }

    /**
     * Reset the trail stack.
     */
    protected function resetTrail()
    {
        $this->trail = [];
    }

    /**
     * Get the count of objects in the trail stack.
     *
     * @return int The amount of objects in the trail stack.
     */
    protected function countTrail()
    {
        return count($this->trail);
    }

    /**
     * Push a final object on the final stack.
     *
     * @param mixed $object
     */
    protected function pushFinal($object)
    {
        array_push($this->final, $object);
    }

    /**
     * Empty the final stack to get the objects inside.
     *
     * @param int $count
     * @return array The content of the final stack.
     */
    protected function emptyFinal(int $count)
    {
        $returnValue = [];

        while ($count > 0) {
            $returnValue[] = array_pop($this->final);
            $count--;
        }

        return array_reverse($returnValue);
    }

    /**
     * Reset the final stack.
     */
    protected function resetFinal()
    {
        $this->final = [];
    }

    /**
     * Mark an object as already processed.
     *
     * @param mixed $object A php object.
     */
    protected function mark($object)
    {
        array_push($this->mark, $object);
    }

    /**
     * Reset the marking of objects.
     */
    protected function resetMark()
    {
        $this->mark = [];
    }

    /**
     * Whether an $object is marked is already processed.
     *
     * @param mixed $object The object to check;
     * @return bool Whether $object is marked.
     */
    protected function isMarked($object)
    {
        return in_array($object, $this->mark, true);
    }

    /**
     * Marshall a QtiComponent that might contain instances of the same class as itself.
     *
     * @param QtiComponent $component The QtiComponent object to marshall.
     * @return DOMElement A DOMElement corresponding to the QtiComponent to marshall.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException If an error occurs during the marshalling process.
     */
    protected function marshall(QtiComponent $component)
    {
        // Reset.
        $this->resetTrail();
        $this->resetFinal();
        $this->resetMark();
        $this->resetProcessed();

        $this->pushTrail($component);

        while ($this->countTrail() > 0) {
            $node = $this->popTrail();

            if (!$node instanceof DOMElement && !$this->isMarked($node) && !$this->isComponentFinal($node)) {
                // Hierarchical node, 1st pass.
                $this->mark($node);
                $this->pushTrail($node); // repush for a further pass.
                $children = array_reverse($this->getChildrenComponents($node)); // next nodes to explore.

                foreach ($children as $c) {
                    $this->pushTrail($c);
                }
            } elseif ($this->isMarked($node)) {
                // Push the result on the trail.
                $finals = $this->emptyFinal(count($this->getChildrenComponents($node)));
                $marshaller = $this->getMarshallerFactory()->createMarshaller($node);
                $element = $marshaller->marshallChildrenKnown($node, $finals);
                $this->pushProcessed($element);

                if ($node === $component) {
                    // This is our second pass on the root element, the process is finished.
                    return $element;
                } else {
                    $this->pushTrail($element);
                }
            } elseif ($node instanceof DOMElement) {
                $this->pushFinal($node);
            } else {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($node);
                $processed = $marshaller->marshall($node);
                $this->pushFinal($processed);
                $this->pushProcessed($processed);
            }
        }
    }

    /**
     * Unmarshall a DOMElement that might contain elements of the same QTI class as itself.
     *
     * @param DOMElement $element The DOMElement object to unmarshall.
     * @param QtiComponent $rootComponent An optional already instantiated QtiComponent to use as the root component.
     * @return QtiComponent A QtiComponent object corresponding to the DOMElement to unmarshall.
     * @throws MarshallerNotFoundException
     */
    protected function unmarshall(DOMElement $element, QtiComponent $rootComponent = null)
    {
        // Reset.
        $this->resetTrail();
        $this->resetFinal();
        $this->resetMark();
        $this->resetProcessed();

        $this->pushTrail($element);

        // Begin the traversing of the n-ary tree... as a graph!
        while ($this->countTrail() > 0) {
            $node = $this->popTrail();

            if (!$node instanceof QtiComponent && !$this->isMarked($node) && !$this->isElementFinal($node)) {
                // Hierarchical node, first pass.
                $this->mark($node);
                $this->pushTrail($node); // repush for a second pass.
                $children = array_reverse($this->getChildrenElements($node)); // further exploration.

                foreach ($children as $c) {
                    $this->pushTrail($c);
                }
            } elseif ($this->isMarked($node)) {
                // Hierarchical node, second pass.

                // Push the result on the trail.
                $finals = $this->emptyFinal(count($this->getChildrenElements($node)));
                $componentCollection = $this->createCollection($node);
                foreach ($finals as $f) {
                    $componentCollection[] = $f;
                }

                $marshaller = $this->getMarshallerFactory()->createMarshaller($node);

                // Root node?
                if ($node === $element && !empty($rootComponent)) {
                    $component = $marshaller->unmarshallChildrenKnown($node, $componentCollection, $rootComponent);
                } elseif ($marshaller instanceof self) {
                    $component = $marshaller->unmarshallChildrenKnown($node, $componentCollection);
                } else {
                    $component = $marshaller->unmarshall($node);
                }

                $this->pushProcessed($component);

                // Root node ?
                if ($node === $element) {
                    // Second pass on the root element, we can return.
                    return $component;
                } else {
                    $this->pushTrail($component);
                }
            } elseif ($node instanceof QtiComponent) {
                $this->pushFinal($node);
            } else {
                if ($node instanceof DOMText) {
                    $node = self::getDOMCradle()->createElement('textRun', preg_replace('/&(?!\w+;)/', '&amp;', $node->wholeText));
                }

                // Process it and make its a final element to be used by hierarchical nodes.
                $marshaller = $this->getMarshallerFactory()->createMarshaller($node);
                $processed = $marshaller->unmarshall($node);
                $this->pushFinal($processed);
                $this->pushProcessed($processed);
            }
        }
    }

    /**
     * Unmarshall a given DOMElement object into a QtiComponent object while
     * receiving the already unmarshalled children components.
     *
     * @param DOMElement $element The actual element to unmarshall.
     * @param QtiComponentCollection $children The already unmarshalled children QTI components.
     * @return QtiComponent $element as a QtiComponent object.
     */
    abstract protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children);

    /**
     * Whether a given $element is final. In other words, whether the $element
     * has child elements.
     *
     * @param DOMNode $element
     */
    abstract protected function isElementFinal(DOMNode $element);

    /**
     * Get the children elements of a given $element.
     *
     * @param DOMElement $element
     * @return array An array of DOMNode objects.
     */
    abstract protected function getChildrenElements(DOMElement $element);

    /**
     * Create a collection from DOMElement objects.
     *
     * @param DOMElement $currentNode
     * @return AbstractCollection
     */
    abstract protected function createCollection(DOMElement $currentNode);

    /**
     * Marshall a given QTI $component while receiving the already marshalled
     * children components.
     *
     * @param QtiComponent $component A QtiComponent object to be marshalled into a DOMElement object.
     * @param array $elements An array of DOMElement objectss.
     * @return DOMElement The marshalled $component.
     */
    abstract protected function marshallChildrenKnown(QtiComponent $component, array $elements);

    /**
     * Whether or not a QtiComponent object is final. In other words, whether $component
     * contains child QtiComponent objects.
     *
     * @param QtiComponent $component
     */
    abstract protected function isComponentFinal(QtiComponent $component);

    /**
     * Get the children components of the given $component.
     *
     * @param QtiComponent $component
     * @return array An array of QtiComponent objects.
     */
    abstract protected function getChildrenComponents(QtiComponent $component);
}
