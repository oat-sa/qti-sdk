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
use qtism\common\utils\Format;
use qtism\data\expressions\RandomFloat;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for randomFloat.
 */
class RandomFloatMarshaller extends Marshaller
{
    /**
     * Marshall a RandomFloat object into a DOMElement object.
     *
     * @param QtiComponent $component A RandomFloat object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());

        $this->setDOMElementAttribute($element, 'min', $component->getMin());
        $this->setDOMElementAttribute($element, 'max', $component->getMax());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI randomFloat element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A RandomFloat object.
     * @throws UnmarshallingException If the mandatory attributes min or max ar missing.
     */
    protected function unmarshall(DOMElement $element)
    {
        // max attribute is mandatory.
        if (($max = $this->getDOMElementAttributeAs($element, 'max')) !== null) {
            $max = (Format::isVariableRef($max)) ? $max : (float)$max;

            $object = new RandomFloat(0.0, $max);

            if (($min = $this->getDOMElementAttributeAs($element, 'min')) !== null) {
                $min = (Format::isVariableRef($min)) ? $min : (float)$min;
                $object->setMin($min);
            }

            return $object;
        } else {
            $msg = "The mandatory attribute 'max' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'randomFloat';
    }
}
