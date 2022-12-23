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
use DOMXPath;
use qtism\data\AssessmentSection;
use qtism\data\content\RubricBlockCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\SectionPartCollection;

/**
 * Marshaller focusing on marshalling/unmarshalling AssessmentSection components.
 */
class AssessmentSectionMarshaller extends RecursiveMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @param AssessmentSection|null $assessmentSection
     * @return AssessmentSection
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(
        DOMElement $element,
        QtiComponentCollection $children,
        AssessmentSection $assessmentSection = null
    ): QtiComponent {
        $baseMarshaller = new SectionPartMarshaller($this->getVersion());
        $baseComponent = $baseMarshaller->unmarshall($element);

        if (($title = $this->getDOMElementAttributeAs($element, 'title')) !== null) {
            if (($visible = $this->getDOMElementAttributeAs($element, 'visible', 'boolean')) !== null) {
                if (empty($assessmentSection)) {
                    $object = new AssessmentSection($baseComponent->getIdentifier(), $title, $visible);
                } else {
                    $object = $assessmentSection;
                    $object->setIdentifier($baseComponent->getIdentifier());
                    $object->setTitle($title);
                    $object->setVisible($visible);
                }

                // One day... We will be able to overload methods in PHP... :'(
                $object->setRequired($baseComponent->isRequired());
                $object->setFixed($baseComponent->isFixed());
                $object->setPreConditions($baseComponent->getPreConditions());
                $object->setBranchRules($baseComponent->getBranchRules());
                $object->setItemSessionControl($baseComponent->getItemSessionControl());
                $object->setTimeLimits($baseComponent->getTimeLimits());

                // Deal with the keepTogether attribute.
                if (($keepTogether = $this->getDOMElementAttributeAs($element, 'keepTogether', 'boolean')) !== null) {
                    $object->setKeepTogether($keepTogether);
                }

                // Deal with selection elements.
                $selectionElements = $this->getChildElementsByTagName($element, 'selection');
                if (count($selectionElements) == 1) {
                    $select = (int)$selectionElements[0]->getAttribute('select');

                    if ($select > 0) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($selectionElements[0]);
                        $object->setSelection($marshaller->unmarshall($selectionElements[0]));
                    }
                }

                // Deal with ordering elements.
                $orderingElements = $this->getChildElementsByTagName($element, 'ordering');
                if (count($orderingElements) == 1) {
                    $marshaller = $this->getMarshallerFactory()->createMarshaller($orderingElements[0]);
                    $object->setOrdering($marshaller->unmarshall($orderingElements[0]));
                }

                // Deal with rubrickBlocks.
                $rubricBlockElements = $this->getChildElementsByTagName($element, 'rubricBlock');
                if (count($rubricBlockElements) > 0) {
                    $rubricBlocks = new RubricBlockCollection();
                    for ($i = 0; $i < count($rubricBlockElements); $i++) {
                        $marshaller = $this->getMarshallerFactory()->createMarshaller($rubricBlockElements[$i]);
                        $rubricBlocks[] = $marshaller->unmarshall($rubricBlockElements[$i]);
                    }

                    $object->setRubricBlocks($rubricBlocks);
                }

                // Deal with section parts... which are known :) !
                $object->setSectionParts($children);

                return $object;
            } else {
                $msg = "The mandatory attribute 'visible' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'title' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $baseMarshaller = new SectionPartMarshaller($this->getVersion());
        $element = $baseMarshaller->marshall($component);

        $this->setDOMElementAttribute($element, 'title', $component->getTitle());
        $this->setDOMElementAttribute($element, 'visible', $component->isVisible());
        $this->setDOMElementAttribute($element, 'keepTogether', $component->mustKeepTogether());

        // Deal with selection element
        $selection = $component->getSelection();
        if (!empty($selection)) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($selection);
            $element->appendChild($marshaller->marshall($selection));
        }

        // Deal with ordering element.
        $ordering = $component->getOrdering();
        if (!empty($ordering)) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($ordering);
            $element->appendChild($marshaller->marshall($ordering));
        }

        // Deal with rubricBlock elements.
        foreach ($component->getRubricBlocks() as $rubricBlock) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($rubricBlock);
            $element->appendChild($marshaller->marshall($rubricBlock));
        }

        // And finally...
        // Deal with sectionPart elements that are actually known...
        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * @param DOMNode $element
     * @return bool
     */
    protected function isElementFinal(DOMNode $element): bool
    {
        return $element->localName != 'assessmentSection';
    }

    /**
     * @param QtiComponent $component
     * @return bool
     */
    protected function isComponentFinal(QtiComponent $component): bool
    {
        return !$component instanceof AssessmentSection;
    }

    /**
     * @param DOMElement $element
     * @return array
     */
    protected function getChildrenElements(DOMElement $element): array
    {
        if ($element->localName == 'assessmentSection') {
            $doc = $element->ownerDocument;
            $xpath = new DOMXPath($doc);
            $nodeList = $xpath->query('assessmentSection | assessmentSectionRef | assessmentItemRef', $element);

            if ($nodeList->length == 0) {
                $xpath->registerNamespace('qti', (string)$doc->lookupNamespaceURI($doc->namespaceURI));
                $nodeList = $xpath->query('qti:assessmentSection | qti:assessmentSectionRef | qti:assessmentItemRef', $element);
            }

            $returnValue = [];

            for ($i = 0; $i < $nodeList->length; $i++) {
                $returnValue[] = $nodeList->item($i);
            }

            return $returnValue;
        } else {
            return [];
        }
    }

    /**
     * @param QtiComponent $component
     * @return array
     */
    protected function getChildrenComponents(QtiComponent $component): array
    {
        if ($component instanceof AssessmentSection) {
            return $component->getSectionParts()->getArrayCopy();
        } else {
            return [];
        }
    }

    /**
     * @param DOMElement $currentNode
     * @return SectionPartCollection
     */
    protected function createCollection(DOMElement $currentNode): SectionPartCollection
    {
        return new SectionPartCollection();
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return '';
    }
}
