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
use qtism\data\expressions\MapResponse;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for mapResponse.
 */
class MapResponseMarshaller extends Marshaller
{
    /**
     * Marshall a MapResponse object into a DOMElement object.
     *
     * @param QtiComponent $component A MapResponse object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI mapResponse element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A MapResponse object.
     * @throws UnmarshallingException If the mandatory attributes 'identifier' is missing.
     */
    protected function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
            return new MapResponse($identifier);
        } else {
            $msg = "The mandatory attribute 'identifier' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'mapResponse';
    }
}
