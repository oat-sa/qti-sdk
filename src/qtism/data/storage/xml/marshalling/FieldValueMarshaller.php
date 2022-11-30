<?php

declare(strict_types=1);

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
use qtism\data\expressions\operators\FieldValue;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of fieldValue QTI operators.
 */
class FieldValueMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall an FieldValue object into a QTI fieldValue element.
     *
     * @param QtiComponent $component The FieldValue object to marshall.
     * @param array An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI fieldValue element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'fieldIdentifier', $component->getFieldIdentifier());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI fieldValue operator element into an FieldValue object.
     *
     * @param DOMElement $element The fieldValue element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent An FieldValue object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($fieldIdentifier = $this->getDOMElementAttributeAs($element, 'fieldIdentifier')) !== null) {
            return new FieldValue($children, $fieldIdentifier);
        } else {
            $msg = "The mandatory attribute 'fieldIdentifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
