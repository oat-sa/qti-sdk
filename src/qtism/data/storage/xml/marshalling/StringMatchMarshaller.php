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
use qtism\data\expressions\operators\StringMatch;
use qtism\data\QtiComponent;
use qtism\data\QtiComponentCollection;

/**
 * A complex Operator marshaller focusing on the marshalling/unmarshalling process
 * of stringMatch QTI operators.
 */
class StringMatchMarshaller extends OperatorMarshaller
{
    /**
     * Unmarshall a StringMatch object into a QTI stringMatch element.
     *
     * @param QtiComponent $component The StringMatch object to marshall.
     * @param array $elements An array of child DOMEelement objects.
     * @return DOMElement The marshalled QTI stringMatch element.
     */
    protected function marshallChildrenKnown(QtiComponent $component, array $elements): DOMElement
    {
        $element = $this->createElement($component);
        $this->setDOMElementAttribute($element, 'caseSensitive', $component->isCaseSensitive());
        $this->setDOMElementAttribute($element, 'substring', $component->mustSubstring());

        foreach ($elements as $elt) {
            $element->appendChild($elt);
        }

        return $element;
    }

    /**
     * Unmarshall a QTI stringMatch operator element into an StringMatch object.
     *
     * @param DOMElement $element The stringMatch element to unmarshall.
     * @param QtiComponentCollection $children A collection containing the child Expression objects composing the Operator.
     * @return QtiComponent An StringMatch object.
     * @throws UnmarshallingException
     */
    protected function unmarshallChildrenKnown(DOMElement $element, QtiComponentCollection $children): QtiComponent
    {
        if (($caseSensitive = $this->getDOMElementAttributeAs($element, 'caseSensitive', 'boolean')) !== null) {
            $object = new StringMatch($children, $caseSensitive);

            if (($substring = $this->getDOMElementAttributeAs($element, 'substring', 'boolean')) !== null) {
                $object->setSubstring($substring);
            }

            return $object;
        } else {
            $msg = "The mandatory attribute 'caseSensitive' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }
}
