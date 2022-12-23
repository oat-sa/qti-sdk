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
use qtism\data\expressions\DefaultVal;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for default.
 */
class DefaultValMarshaller extends Marshaller
{
    /**
     * Marshall a DefaultVal object into a DOMElement object.
     *
     * @param QtiComponent $component A DefaultVal object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI default element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return DefaultVal A DefaultVal object.
     * @throws UnmarshallingException If the mandatory attributes 'identifier' is missing.
     */
    protected function unmarshall(DOMElement $element): DefaultVal
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
            return new DefaultVal($identifier);
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'default';
    }
}
