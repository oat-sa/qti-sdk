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
use qtism\common\datatypes\QtiShape;
use qtism\data\expressions\operators\Inside;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;
use qtism\data\storage\Utils;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of inside QTI operators.
 */
class InsideMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall an Inside object into a QTI inside element.
     *
     * @param QtiComponent $component The Inside object to marshall.
     * @param array An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI inside element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'shape', QtiShape::getNameByConstant($component->getShape()));
        $this->setDOMElementAttribute($element, 'coords', $component->getCoords());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI inside operator element into an Inside object.
     *
     * @param DOMElement $element The inside element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent An Inside object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($shape = $this->getDOMElementAttributeAs($element, 'shape')) !== null) {
            if (($coords = $this->getDOMElementAttributeAs($element, 'coords')) !== null) {
                $shape = QtiShape::getConstantByName($shape);
                $coords = Utils::stringToCoords($coords, $shape);

                return new Inside($children, $shape, $coords);
            } else {
                $msg = "The mandatory attribute 'coords' is missing from element '" . $element->localName . "'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory attribute 'shape' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
