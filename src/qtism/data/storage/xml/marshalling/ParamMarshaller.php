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
use qtism\data\content\xhtml\Param;
use qtism\data\content\xhtml\ParamType;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for Param.
 */
class ParamMarshaller extends Marshaller
{
    /**
     * Marshall a Param object into a DOMElement object.
     *
     * @param QtiComponent $component A Param object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'name', $component->getName());
        $this->setDOMElementAttribute($element, 'value', $component->getValue());
        $this->setDOMElementAttribute($element, 'valuetype', ParamType::getNameByConstant($component->getValueType()));

        if ($component->hasType() === true) {
            $this->setDOMElementAttribute($element, 'type', $component->getType());
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an XHTML param element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Param object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($name = $this->getDOMElementAttributeAs($element, 'name')) === null) {
            // XSD use="required" but can be empty.
            $name = '';
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'value')) === null) {
            // XSD use="required" but can be empty.
            $value = '';
        }

        if (($valueType = $this->getDOMElementAttributeAs($element, 'valuetype')) !== null) {
            $component = new Param($name, $value, ParamType::getConstantByName($valueType));

            if (($type = $this->getDOMElementAttributeAs($element, 'type')) !== null) {
                $component->setType($type);
            }

            return $component;
        } else {
            $msg = "The mandatory attribute 'valueType' is missing from the 'param' element.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'param';
    }
}
