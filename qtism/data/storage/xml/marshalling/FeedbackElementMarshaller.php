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
use qtism\data\content\Flow;
use qtism\data\content\FlowCollection;
use qtism\data\content\InlineCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\ShowHide;

/**
 * The Marshaller implementation for FeedbackInline/FeedbackBlock elements of the content model.
 */
class FeedbackElementMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $fqClass = $this->lookupClass($element);

        if (($outcomeIdentifier = $this->getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
            if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
                $component = new $fqClass($outcomeIdentifier, $identifier);

                if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                    try {
                        $component->setShowHide(ShowHide::getConstantByName($showHide));
                    } catch (InvalidArgumentException $e) {
                        $msg = "'${showHide}' is not a valid value for the 'showHide' attribute of element '" . $element->localName . "'.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }

                    $inline = $element->localName === 'feedbackInline';
                    $content = ($inline === true) ? new InlineCollection() : new FlowCollection();
                    $blockExclusion = ['hottext', 'rubricBlock', 'endAttemptInteraction', 'inlineChoiceInteraction', 'textEntryInteraction'];
                    foreach ($children as $child) {
                        $qtiClassName = $child->getQtiClassName();
                        if ($inline === false && !$child instanceof Flow) {
                            $msg = "A '${qtiClassName}' cannot be contained by a 'feedbackBlock'.";
                            throw new UnmarshallingException($msg, $element);
                        }
                        if ($inline === false && in_array($child->getQtiClassName(), $blockExclusion)) {
                            $msg = "A '${qtiClassName}' cannot be contained by a 'feedbackBlock'.";
                            throw new UnmarshallingException($msg, $element);
                        }

                        $content[] = $child;
                    }

                    $component->setContent($content);

                    if (($xmlBase = self::getXmlBase($element)) !== false) {
                        $component->setXmlBase($xmlBase);
                    }

                    $this->fillBodyElement($component, $element);

                    return $component;
                }
            } else {
                $msg = "The mandatory 'identifier' attribute is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'outcomeIdentifier' attribute is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @param QtiComponent $component
     * @param array $elements
     * @return DOMElement
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = ["qtism\\data\\content"];
    }
}
