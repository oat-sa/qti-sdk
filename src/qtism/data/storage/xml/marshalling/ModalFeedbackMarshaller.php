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
use InvalidArgumentException;
use qtism\data\content\FlowStaticCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\ShowHide;

/**
 * The Marshaller implementation for ModalFeedback elements of the content model.
 */
class ModalFeedbackMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        $fqClass = $this->lookupClass($element);

        if (($outcomeIdentifier = $this->getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
            if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
                $component = new $fqClass($outcomeIdentifier, $identifier);

                if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                    try {
                        $component->setShowHide(ShowHide::getConstantByName($showHide));
                    } catch (InvalidArgumentException $e) {
                        $msg = "'${showHide}' is not a valid value for the 'showHide' attribute of element 'modalFeedback'.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }

                    try {
                        $content = new FlowStaticCollection($children->getArrayCopy());
                        $component->setContent($content);
                    } catch (InvalidArgumentException $e) {
                        $msg = "The content of the 'modalFeedback' is invalid. It must only contain 'flowStatic' elements.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }

                    if (($title = $this->getDOMElementAttributeAs($element, 'title')) !== null) {
                        $component->setTitle($title);
                    }

                    return $component;
                } else {
                    $msg = "The mandatory 'showHide' attribute is missing from element 'modalFeedback'.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory 'identifier' attribute is missing from element 'modalFeedback'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'outcomeIdentifier' attribute is missing from element 'modalFeedback'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));

        if ($component->hasTitle() === true) {
            $this->setDOMElementAttribute($element, 'title', $component->getTitle());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses(): void
    {
        $this->lookupClasses = ["qtism\\data\\content"];
    }
}
