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
use qtism\data\expressions\operators\AnyN;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of anyN QTI operators.
 */
class AnyNMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall an AnyN object into a QTI anyN element.
     *
     * @param QtiComponent $component The AnyN object to marshall.
     * @param DOMElement[] $elements An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI anyN element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'min', $component->getMin());
        $this->setDOMElementAttribute($element, 'max', $component->getMax());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI anyN operator element into an AnyN object.
     *
     * @param DOMElement $element The anyN element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent An AnyN object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($min = $this->getDOMElementAttributeAs($element, 'min')) !== null) {
            if (Format::isInteger($min)) {
                $min = (int)$min;
            }

            if (($max = $this->getDOMElementAttributeAs($element, 'max')) !== null) {
                if (Format::isInteger($max)) {
                    $max = (int)$max;
                }

                return new AnyN($children, $min, $max);
            } else {
                $msg = "The mandatory attribute 'max' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'min' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
