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
use qtism\data\expressions\operators\Statistics;
use qtism\data\expressions\operators\StatsOperator;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of StatOperators QTI operators.
 */
class StatsOperatorMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall a StatsOperator object into a QTI statsOperator element.
     *
     * @param QtiComponent $component The StatsOperator object to marshall.
     * @param array $elements An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI statsOperator element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'name', Statistics::getNameByConstant($component->getName()));

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI statsOperator operator element into a StatsOperator object.
     *
     * @param DOMElement $element The statsOperator element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent A StatsOperator object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($name = $this->getDOMElementAttributeAs($element, 'name')) !== null) {
            return new StatsOperator($children, Statistics::getConstantByName($name));
        } else {
            $msg = "The mandatory attribute 'name' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
