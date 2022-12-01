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
use qtism\data\content\interactions\Orientation;
use qtism\data\content\interactions\SliderInteraction;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for SliderInteraction.
 */
class SliderInteractionMarshaller extends Marshaller
{
    /**
     * Marshall a SliderInteraction object into a DOMElement object.
     *
     * @param QtiComponent $component A SliderInteraction object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());
        $this->setDOMElementAttribute($element, 'lowerBound', $component->getLowerBound());
        $this->setDOMElementAttribute($element, 'upperBound', $component->getUpperBound());

        if ($component->hasStep() === true) {
            $this->setDOMElementAttribute($element, 'step', $component->getStep());
        }

        if ($component->mustStepLabel() === true) {
            $this->setDOMElementAttribute($element, 'stepLabel', true);
        }

        if ($component->getOrientation() === Orientation::VERTICAL) {
            $this->setDOMElementAttribute($element, 'orientation', Orientation::getNameByConstant(Orientation::VERTICAL));
        }

        if ($component->mustReverse() === true) {
            $this->setDOMElementAttribute($element, 'reverse', true);
        }

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        if ($component->hasPrompt() === true) {
            $element->appendChild($this->getMarshallerFactory()->createMarshaller($component->getPrompt())->marshall($component->getPrompt()));
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a SliderInteraction element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A SliderInteraction object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): SliderInteraction
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            if (($lowerBound = $this->getDOMElementAttributeAs($element, 'lowerBound', 'float')) !== null) {
                if (($upperBound = $this->getDOMElementAttributeAs($element, 'upperBound', 'float')) !== null) {
                    $component = new SliderInteraction($responseIdentifier, $lowerBound, $upperBound);

                    $promptElts = $this->getChildElementsByTagName($element, 'prompt');
                    if (count($promptElts) > 0) {
                        $promptElt = $promptElts[0];
                        $prompt = $this->getMarshallerFactory()->createMarshaller($promptElt)->unmarshall($promptElt);
                        $component->setPrompt($prompt);
                    }

                    if (($step = $this->getDOMElementAttributeAs($element, 'step', 'integer')) !== null) {
                        $component->setStep($step);
                    }

                    if (($stepLabel = $this->getDOMElementAttributeAs($element, 'stepLabel', 'boolean')) !== null) {
                        $component->setStepLabel($stepLabel);
                    }

                    if (($orientation = $this->getDOMElementAttributeAs($element, 'orientation')) !== null) {
                        try {
                            $component->setOrientation(Orientation::getConstantByName($orientation));
                        } catch (InvalidArgumentException $e) {
                            $msg = "The value of the 'orientation' attribute of the 'sliderInteraction' is invalid.";
                            throw new UnmarshallingException($msg, $element, $e);
                        }
                    }

                    if (($reverse = $this->getDOMElementAttributeAs($element, 'reverse', 'boolean')) !== null) {
                        $component->setReverse($reverse);
                    }

                    if (($xmlBase = self::getXmlBase($element)) !== false) {
                        $component->setXmlBase($xmlBase);
                    }

                    $this->fillBodyElement($component, $element);

                    return $component;
                } else {
                    $msg = "The mandatory 'upperBound' attribute is missing from the 'sliderInteraction' element.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory 'lowerBound' attribute is missing from the 'sliderInteraction' element.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'responseIdentifier' attribute is missing from the 'sliderInteraction' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'sliderInteraction';
    }
}
