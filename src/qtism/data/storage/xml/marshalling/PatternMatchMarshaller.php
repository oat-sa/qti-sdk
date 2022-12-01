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
use qtism\data\expressions\operators\PatternMatch;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of patternMatch QTI operators.
 */
class PatternMatchMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall a PatternMatch object into a QTI patternMatch element.
     *
     * @param QtiComponent $component The PatternMatch object to marshall.
     * @param array $elements An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI patternMatch element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'pattern', $component->getPattern());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI patternMatch operator element into an PatternMatch object.
     *
     * @param DOMElement $element The patternMatch element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent A PatternMatch object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($pattern = $this->getDOMElementAttributeAs($element, 'pattern')) !== null) {
            return new PatternMatch($children, $pattern);
        } else {
            $msg = "The mandatory attribute 'pattern' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
