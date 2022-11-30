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
use qtism\common\utils\Format;
use qtism\data\expressions\operators\EqualRounded;
use qtism\data\expressions\operators\RoundingMode;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of EqualRounded QTI operators.
 */
class EqualRoundedMarshaller extends OperatorMarshaller
{
    /**
     * Marshall an EqualRounded object into a QTI equalRounded element.
     *
     * @param QtiComponent $component The EqualRounded object to marshall.
     * @param array An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI equalRounded element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'roundingMode', RoundingMode::getNameByConstant($component->getRoundingMode()));
        $this->setDOMElementAttribute($element, 'figures', $component->getFigures());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI equalRounded operator element into an EqualRounded object.
     *
     * @param DOMElement $element The EqualRounded element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent An EqualRounded object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($figures = $this->getDOMElementAttributeAs($element, 'figures')) !== null) {
            if (Format::isInteger($figures)) {
                $figures = (int)$figures;
            }

            $object = new EqualRounded($children, $figures);

            if (($roundingMode = $this->getDOMElementAttributeAs($element, 'roundingMode')) !== null) {
                $object->setRoundingMode(RoundingMode::getConstantByName($roundingMode));
            }

            return $object;
        } else {
            $msg = "The mandatory attribute 'figures' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
