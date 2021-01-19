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
 * The Marshaller implementation for GapChoice(gapText/gapImg) elements of the content model.
 */
class GapChoiceMarshaller extends ContentMarshaller
{
    private static $gapTextAllowedContent = [
        'a',
        'abbr',
        'acronym',
        'b',
        'big',
        'br',
        'cite',
        'code',
        'dfn',
        'em',
        'feedbackInline',
        'i',
        'img',
        'include',
        'kbd',
        'math',
        'object',
        'printedVariable',
        'q',
        'samp',
        'small',
        'span',
        'strong',
        'sub',
        'sup',
        'templateInline',
        'textRun',
        'tt',
        'var',
    ];

    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $expectedGapImgName = 'gapImg';

        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($matchMax = $this->getDOMElementAttributeAs($element, 'matchMax', 'integer')) !== null) {
                $fqClass = $this->lookupClass($element);

                if ($element->localName === $expectedGapImgName) {
                    if (count($children) === 1) {
                        $component = new $fqClass($identifier, $matchMax, $children[0]);
                    } else {
                        $msg = "A 'gapImg' element must contain a single 'object' element, " . count($children) . ' given.';
                        throw new UnmarshallingException($msg, $element);
                    }
                } else {
                    $component = new $fqClass($identifier, $matchMax);
                }

                if (($matchMin = $this->getDOMElementAttributeAs($element, 'matchMin', 'integer')) !== null) {
                    $component->setMatchMin($matchMin);
                }

                if (($fixed = $this->getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                    $component->setFixed($fixed);
                }

                if (($templateIdentifier = $this->getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
                    $component->setTemplateIdentifier($templateIdentifier);
                }

                if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                    $component->setShowHide(ShowHide::getConstantByName($showHide));
                }

                if ($element->localName === 'gapText') {
                    // The allowed set of elements in a gapText (for QTI 2.2) is a subset of FlowStatic.
                    // Let's make sure that children elements are in this subset.
                    $childrenArray = $children->getArrayCopy();

                    foreach ($childrenArray as $child) {
                        if (in_array($child->getQtiClassName(), self::$gapTextAllowedContent) === false) {
                            throw new UnmarshallingException(
                                "Element with class '" . $child->getQtiClassName() . "' is not allowed in 'gapText' elements.",
                                $element
                            );
                        }
                    }

                    try {
                        $component->setContent(new FlowStaticCollection($childrenArray));
                    } catch (InvalidArgumentException $e) {
                        $msg = "Invalid content in 'gapText' element.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }
                } elseif (($objectLabel = $this->getDOMElementAttributeAs($element, 'objectLabel')) !== null) {
                    $component->setObjectLabel($objectLabel);
                }

                $this->fillBodyElement($component, $element);

                return $component;
            } else {
                $msg = "The mandatory 'matchMax' attribute is missing from the 'simpleChoice' element.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from the 'simpleChoice' element.";
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
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->fillElement($element, $component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'matchMax', $component->getMatchMax());

        if ($component->getMatchMin() !== 0) {
            $this->setDOMElementAttribute($element, 'matchMin', $component->getMatchMin());
        }

        if ($component->isFixed() === true) {
            $this->setDOMElementAttribute($element, 'fixed', true);
        }

        if ($component->hasTemplateIdentifier() === true) {
            $this->setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if ($component->getShowHide() !== ShowHide::SHOW) {
            $this->setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant(ShowHide::HIDE));
        }

        if ($element->localName === 'gapImg' && $component->hasObjectLabel() === true) {
            $this->setDOMElementAttribute($element, 'objectLabel', $component->getObjectLabel());
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses()
    {
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
