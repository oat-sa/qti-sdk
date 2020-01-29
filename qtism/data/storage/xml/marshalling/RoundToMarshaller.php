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
use qtism\data\expressions\operators\RoundingMode;
use qtism\data\expressions\operators\RoundTo;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of RoundTo QTI operators.
 */
class RoundToMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall a RoundTo object into a QTI roundTo element.
     *
     * @param QtiComponent $component The RoundTo object to marshall.
     * @param array $elements An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI roundTo element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = self::getDOMCradle()->createElement($component->getQtiClassName());

        self::setDOMElementAttribute($element, 'figures', $component->getFigures());
        self::setDOMElementAttribute($element, 'roundingMode', RoundingMode::getNameByConstant($component->getRoundingMode()));

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI roundTo operator element into a RoundTo object.
     *
     * @param DOMElement $element The roundTo element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent A RoundTo object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($figures = static::getDOMElementAttributeAs($element, 'figures', 'string')) !== null) {
            if (!Format::isVariableRef($figures)) {
                $figures = intval($figures);
            }

            $object = new RoundTo($children, $figures);

            if (($roundingMode = static::getDOMElementAttributeAs($element, 'roundingMode')) !== null) {
                $object->setRoundingMode(RoundingMode::getConstantByName($roundingMode));
            }

            return $object;
        } else {
            $msg = "The mandatory attribute 'figures' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
