<?php

declare(strict_types=1);

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
use qtism\common\collections\IdentifierCollection;
use qtism\common\utils\Version;
use qtism\data\content\FlowStaticCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\ShowHide;

/**
 * The Marshaller implementation for SimpleAssociableChoice elements of the content model.
 */
class SimpleAssociableChoiceMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        $version = $this->getVersion();

        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($matchMax = $this->getDOMElementAttributeAs($element, 'matchMax', 'integer')) !== null) {
                $fqClass = $this->lookupClass($element);
                $component = new $fqClass($identifier, $matchMax);

                if (($fixed = $this->getDOMElementAttributeAs($element, 'fixed', 'boolean')) !== null) {
                    $component->setFixed($fixed);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($templateIdentifier = $this->getDOMElementAttributeAs($element, 'templateIdentifier')) !== null) {
                    $component->setTemplateIdentifier($templateIdentifier);
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                    $component->setShowHide(ShowHide::getConstantByName($showHide));
                }

                if (Version::compare($version, '2.1.0', '>=') === true && ($matchMin = $this->getDOMElementAttributeAs($element, 'matchMin', 'integer')) !== null) {
                    $component->setMatchMin($matchMin);
                }

                if (Version::compare($version, '2.1.0', '<') === true && ($matchGroup = $this->getDOMElementAttributeAs($element, 'matchGroup')) !== null) {
                    $component->setMatchGroup(new IdentifierCollection(explode("\x20", $matchGroup)));
                }

                $component->setContent(new FlowStaticCollection($children->getArrayCopy()));
                $this->fillBodyElement($component, $element);

                return $component;
            } else {
                $msg = "The mandatory 'matchMax' attribute is missing from the 'simpleAssociableChoice' element.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from the 'simpleAssociableChoice' element.";
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
        $version = $this->getVersion();
        $element = $this->createElement($component);
        $this->fillElement($element, $component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'matchMax', $component->getMatchMax());

        if ($component->isFixed() === true) {
            $this->setDOMElementAttribute($element, 'fixed', true);
        }

        if ($component->hasTemplateIdentifier() === true && Version::compare($version, '2.1.0', '>=') === true) {
            $this->setDOMElementAttribute($element, 'templateIdentifier', $component->getTemplateIdentifier());
        }

        if ($component->getShowHide() !== ShowHide::SHOW && Version::compare($version, '2.1.0', '>=') === true) {
            $this->setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant(ShowHide::HIDE));
        }

        if ($component->getMatchMin() !== 0 && Version::compare($version, '2.1.0', '>=') === true) {
            $this->setDOMElementAttribute($element, 'matchMin', $component->getMatchMin());
        }

        if (Version::compare($version, '2.1.0', '<') === true) {
            $matchGroup = $component->getMatchGroup();
            if (count($matchGroup) > 0) {
                $this->setDOMElementAttribute($element, 'matchGroup', implode(' ', $matchGroup->getArrayCopy()));
            }
        }

        foreach ($elements as $e) {
            $element->appendChild($e);
        }

        return $element;
    }

    protected function setLookupClasses(): void
    {
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
