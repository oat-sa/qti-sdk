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
use qtism\data\rules\PreCondition;

/**
 * Marshalling/Unmarshalling implementation for preCondition.
 */
class PreConditionMarshaller extends Marshaller
{
    /**
     * Marshall a PreCondition object into a DOMElement object.
     *
     * @param QtiComponent $component A PreCondition object.
     * @return DOMElement The according DOMElement object.
     * @throws MarshallerNotFoundException
     * @throws MarshallingException
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $marshaller = $this->getMarshallerFactory()->createMarshaller($component->getExpression());
        $element->appendChild($marshaller->marshall($component->getExpression()));

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI preCondition element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return Precondition A Precondition object.
     * @throws MarshallerNotFoundException
     * @throws UnmarshallingException If $element does not contain any QTI expression element.
     */
    protected function unmarshall(DOMElement $element): Precondition
    {
        $expressionElt = self::getFirstChildElement($element);

        if ($expressionElt !== false) {
            $marshaller = $this->getMarshallerFactory()->createMarshaller($expressionElt);
            return new PreCondition($marshaller->unmarshall($expressionElt));
        } else {
            $msg = "The mandatory 'expression' child element is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'preCondition';
    }
}
