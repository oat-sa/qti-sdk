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
use qtism\data\content\interactions\ChoiceInteraction;
use qtism\data\content\interactions\OrderInteraction;
use qtism\data\content\interactions\Orientation;
use qtism\data\content\interactions\SimpleChoiceCollection;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * The Marshaller implementation for ChoiceInteraction/OrderInteraction elements of the content model.
 */
class ChoiceInteractionMarshaller extends ContentMarshaller
{
    /**
     * @param DOMElement $element
     * @param QtiComponentCollection $children
     * @return mixed
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        $expectedOrderInteractionClassName = 'orderInteraction';
        $isOrderInteraction = $element->localName === $expectedOrderInteractionClassName;

        // responseIdentifier.
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $fqClass = $this->lookupClass($element);
            $component = new $fqClass($responseIdentifier, new SimpleChoiceCollection($children->getArrayCopy()));

            if (($shuffle = $this->getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
                $component->setShuffle($shuffle);
            }

            // maxChoices.
            if (($maxChoices = $this->getDOMElementAttributeAs($element, 'maxChoices', 'integer')) !== null) {
                if ($isOrderInteraction) {
                    if ($maxChoices !== 0) {
                        $component->setMaxChoices($maxChoices);
                    }
                } else {
                    $component->setMaxChoices($maxChoices);
                }
            }

            // minChoices.
            if (($minChoices = $this->getDOMElementAttributeAs($element, 'minChoices', 'integer')) !== null) {
                if ($isOrderInteraction) {
                    /*
                     * Lots of QTI implementations output minChoices = 0 while
                     * dealing with orderInteraction unmarshalling. However, regarding
                     * the IMS QTI Specification, it is invalid.
                     *
                     * "If specified, minChoices must be 1 or greater but must not exceed the
                     * number of choices available."
                     *
                     * See http://www.imsglobal.org/question/qtiv2p1/imsqti_infov2p1.html#element10283
                     */
                    if ($minChoices !== 0) {
                        $component->setMinChoices($minChoices);
                    }
                } else {
                    $component->setMinChoices($minChoices);
                }
            }

            // orientation.
            $orientation = $this->getDOMElementAttributeAs($element, 'orientation');
            if ($orientation !== null) {
                $component->setOrientation(Orientation::getConstantByName($orientation));
            }

            if (($xmlBase = self::getXmlBase($element)) !== false) {
                $component->setXmlBase($xmlBase);
            }

            $promptElts = $this->getChildElementsByTagName($element, 'prompt');
            if (count($promptElts) > 0) {
                $promptElt = $promptElts[0];
                $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
                $component->setPrompt($prompt);
            }

            $this->fillBodyElement($component, $element);

            return $component;
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the " . $element->localName . ' element.';
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
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $isOrderInteraction = $component instanceof OrderInteraction;
        $isChoiceInteraction = $component instanceof ChoiceInteraction;
        
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        // prompt.
        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        // shuffle.
        if ($component->mustShuffle() !== false) {
            $this->setDOMElementAttribute($element, 'shuffle', true);
        }

        // maxChoices.
        if (($isChoiceInteraction && $component->getMaxChoices() !== 1) || ($isOrderInteraction && $component->getMaxChoices() !== -1)) {
            $this->setDOMElementAttribute($element, 'maxChoices', $component->getMaxChoices());
        }

        // minChoices.
        if (($isChoiceInteraction && $component->getMinChoices() !== 0) || ($isOrderInteraction && $component->getMinChoices() !== -1)) {
            $this->setDOMElementAttribute($element, 'minChoices', $component->getMinChoices());
        }

        // orientation.
        if ($component->getOrientation() !== Orientation::VERTICAL) {
            $this->setDOMElementAttribute($element, 'orientation', Orientation::getNameByConstant(Orientation::HORIZONTAL));
        }

        // xml:base.
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
        $this->lookupClasses = ["qtism\\data\\content\\interactions"];
    }
}
