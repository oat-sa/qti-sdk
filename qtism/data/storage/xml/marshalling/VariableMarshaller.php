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
use qtism\data\expressions\Variable;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for variable.
 */
class VariableMarshaller extends Marshaller
{
    /**
     * Marshall a Variable object into a DOMElement object.
     *
     * @param QtiComponent $component A Variable object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        $weightIdentifier = $component->getWeightIdentifier();
        if (!empty($weightIdentifier)) {
            $this->setDOMElementAttribute($element, 'weightIdentifier', $weightIdentifier);
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI Variable element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Variable object.
     * @throws UnmarshallingException If the mandatory attribute 'identifier' is not set in $element.
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $object = new Variable($identifier);

            if (($weightIdentifier = $this->getDOMElementAttributeAs($element, 'weightIdentifier')) !== null) {
                $object->setWeightIdentifier($weightIdentifier);
            }

            return $object;
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
        return 'variable';
    }
}
