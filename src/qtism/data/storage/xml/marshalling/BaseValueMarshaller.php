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
use qtism\common\enums\BaseType;
use qtism\data\expressions\BaseValue;
use qtism\data\QtiComponent;
use qtism\data\storage\Utils;

/**
 * Marshalling/Unmarshalling implementation for BaseValue.
 */
class BaseValueMarshaller extends Marshaller
{
    /**
     * Marshall a BaseValue object into a DOMElement object.
     *
     * @param QtiComponent $component A BaseValue object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'baseType', BaseType::getNameByConstant($component->getBaseType()));
        self::setDOMElementValue($element, $component->getValue());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI baseValue element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A BaseValue object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($baseType = $this->getDOMElementAttributeAs($element, 'baseType', 'string')) !== null) {
            $value = $element->nodeValue;
            $baseTypeCst = BaseType::getConstantByName($baseType);

            // A little bit of cleaning...
            if ($baseTypeCst !== BaseType::STRING) {
                $value = trim($value);
            }

            return new BaseValue($baseTypeCst, Utils::stringToDatatype($value, $baseTypeCst));
        } else {
            $msg = "The mandatory attribute 'baseType' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'baseValue';
    }
}
