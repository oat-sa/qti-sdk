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
use qtism\common\utils\Format;
use qtism\data\QtiComponent;
use qtism\data\state\Weight;

/**
 * Marshalling/Unmarshalling implementation for weight.
 */
class WeightMarshaller extends Marshaller
{
    /**
     * Marshall a Weight object into a DOMElement object.
     *
     * @param QtiComponent $component A Weight object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'value', $component->getValue());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI weight element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Weight object.
     * @throws UnmarshallingException If the mandatory attributes 'identifier' or 'value' are missing from $element but also if 'value' cannot be converted to a float value or 'identifier' is not a valid QTI Identifier.
     */
    protected function unmarshall(DOMElement $element)
    {
        // identifier is a mandatory value.
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
            if (($value = $this->getDOMElementAttributeAs($element, 'value', 'string')) !== null) {
                if (Format::isFloat($value)) {
                    try {
                        return new Weight($identifier, (float)$value);
                    } catch (InvalidArgumentException $e) {
                        $msg = "The value of 'identifier' from element '" . $element->localName . "' is not a valid QTI Identifier.";
                        throw new UnmarshallingException($msg, $element, $e);
                    }
                } else {
                    $msg = "The value of attribute 'value' from element '" . $element->localName . "' cannot be converted into a float.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory attribute 'value' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'weight';
    }
}
