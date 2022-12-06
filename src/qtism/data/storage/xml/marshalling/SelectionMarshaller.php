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
use qtism\data\rules\Selection;
use qtism\data\storage\xml\Utils;

/**
 * Marshalling/Unmarshalling implementation for selection.
 */
class SelectionMarshaller extends Marshaller
{
    /**
     * Marshall a Selection object into a DOMElement object.
     *
     * @param QtiComponent $component A Selection object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'select', $component->getSelect());
        $this->setDOMElementAttribute($element, 'withReplacement', $component->isWithReplacement());

        if (($xml = $component->getXml()) !== null) {
            $selectionElt = $xml->documentElement->cloneNode(true);

            Utils::importChildNodes($selectionElt, $element);
            Utils::importAttributes($selectionElt, $element);
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI Selection object.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Selection object.
     * @throws UnmarshallingException If the mandatory 'select' attribute is missing from $element.
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): Selection
    {
        // Retrieve XML content as a string.
        $frag = $element->ownerDocument->createDocumentFragment();
        $element = $element->cloneNode(true);
        $frag->appendChild($element);
        $xmlString = $frag->ownerDocument->saveXML($frag);

        // select is a mandatory value, retrieve it first.
        if (($value = $this->getDOMElementAttributeAs($element, 'select', 'integer')) !== null) {
            $withReplacement = false;

            if (($withReplacementValue = $this->getDOMElementAttributeAs($element, 'withReplacement', 'boolean')) !== null) {
                $withReplacement = $withReplacementValue;
            }

            $object = new Selection($value, $withReplacement, $xmlString);
        } else {
            $msg = "The mandatory attribute 'select' is missing.";
            throw new UnmarshallingException($msg, $element);
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'selection';
    }
}
