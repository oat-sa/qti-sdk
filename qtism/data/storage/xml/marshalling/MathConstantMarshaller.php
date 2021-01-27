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
use qtism\data\expressions\MathConstant;
use qtism\data\expressions\MathEnumeration;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for mathConstant.
 */
class MathConstantMarshaller extends Marshaller
{
    /**
     * Marshall a MathConstant object into a DOMElement object.
     *
     * @param QtiComponent $component A MathConstant object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'name', MathEnumeration::getNameByConstant($component->getName()));

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI mathConstant element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A MathConstant object.
     * @throws UnmarshallingException If the mandatory attribute 'name' is missing.
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($name = $this->getDOMElementAttributeAs($element, 'name')) !== null) {
            if (($cst = MathEnumeration::getConstantByName($name)) !== false) {
                return new MathConstant($cst);
            } else {
                $msg = "'${name}' is not a valid value for the attribute 'name' from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'name' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'mathConstant';
    }
}
