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
use qtism\data\expressions\operators\Repeat;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of repeat QTI operators.
 */
class RepeatMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall a Repeat object into a QTI repeat element.
     *
     * @param QtiComponent $component The Repeat object to marshall.
     * @param array $elements An array of child DOMElement objects.
     * @return DOMElement The marshalled QTI repeat element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements)
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'numberRepeats', $component->getNumberRepeats());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI repeat operator element into a Repeat object.
     *
     * @param DOMElement $element The repeat element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent A Repeat object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children)
    {
        if (($numberRepeats = $this->getDOMElementAttributeAs($element, 'numberRepeats')) !== null) {
            if (Format::isInteger($numberRepeats)) {
                $numberRepeats = (int)$numberRepeats;
            }

            return new Repeat($children, $numberRepeats);
        } else {
            $msg = "The mandatory attribute 'numberRepeats' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
