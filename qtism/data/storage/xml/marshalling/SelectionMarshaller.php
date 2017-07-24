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
 * Copyright (c) 2013 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts, <jerome@taotesting.com>
 * @license GPLv2
 * @package
 */


namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\rules\Selection;
use \DOMElement;
use qtism\data\SectionPartCollection;

/**
 * Marshalling/Unmarshalling implementation for selection.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class SelectionMarshaller extends RecursiveMarshaller {

    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());

        self::setDOMElementAttribute($element, 'select', $component->getSelect());
        self::setDOMElementAttribute($element, 'withReplacement', $component->isWithReplacement());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @param Selection|null $selection
     * @return Selection
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(
        DOMElement $element,
        QtiComponentCollection $children,
        Selection $selection = null
    ) {
        if (($value = static::getDOMElementAttributeAs($element, 'select', 'integer')) !== null) {
            if (empty($selection)) {
                $object = new Selection($value);
            } else {
                $object = $selection;
            }
            if (($value = static::getDOMElementAttributeAs($element, 'withReplacement', 'boolean')) !== null) {
                $object->setWithReplacement($value);
            }
            $object->setSectionParts($children);
            return $object;
        } else {
            $msg = "The mandatory attribute 'select' is missing.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'selection';
    }

    /**
     *
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element)
    {
        if ($element->localName == 'selection') {

            $doc = $element->ownerDocument;
            $xpath = new \DOMXPath($doc);
            $nodeList = $xpath->query('adaptiveItemSelection', $element);

            if ($nodeList->length == 0) {
                $xpath->registerNamespace('ais', $doc->lookupNamespaceURI($doc->namespaceURI));
                $nodeList = $xpath->query('ais:adaptiveItemSelection', $element);
            }
            $returnValue = array();

            for ($i = 0; $i < $nodeList->length; $i++) {
                $returnValue[] = $nodeList->item($i);
            }

            return $returnValue;
        }
        else {
            return array();
        }
    }

    /**
     * @param \DOMNode $element
     * @return bool
     */
    protected function isElementFinal(\DOMNode $element) {
        return $element->localName != 'selection';
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component) {
        return !$component instanceof Selection;
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component) {
        if ($component instanceof Selection) {
            return $component->getSectionParts()->getArrayCopy();
        }
        else {
            return array();
        }
    }

    /**
     * @param DOMElement $currentNode
     * @return SectionPartCollection
     */
    protected function createCollection(DOMElement $currentNode) {
        return new SectionPartCollection();
    }


}
