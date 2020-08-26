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
use qtism\data\QtiComponent;
use qtism\data\rules\SetTemplateValue;

/**
 * Marshalling/Unmarshalling implementation for setTemplateValue.
 */
class SetTemplateValueMarshaller extends Marshaller
{
    /**
     * Marshall a SetTemplateValue object into a DOMElement object.
     *
     * @param QtiComponent $component A SetTemplateValue object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());
        $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
        $element->appendChild($marshaller->marshall($component->getExpression()));

        static::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI setTemplateValue element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A SetTemplateValue object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($identifier = static::getDOMElementAttributeAs($element, 'identifier')) !== null) {
            $expressionElt = self::getFirstChildElement($element);

            if ($expressionElt !== false) {
                $marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
                return new SetTemplateValue($identifier, $marshaller->unmarshall($expressionElt));
            } else {
                $msg = "The mandatory child element 'expression' is missing from element 'setTemplateValue'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element 'setTemplateValue'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'setTemplateValue';
    }
}
