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
use qtism\data\expressions\Correct;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for correct.
 */
class CorrectMarshaller extends Marshaller
{
    /**
     * Marshall a Correct object into a DOMElement object.
     *
     * @param QtiComponent $component A Correct object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI correct element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Correct object.
     * @throws UnmarshallingException
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): Correct
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier', 'string')) !== null) {
            return new Correct($identifier);
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
        return 'correct';
    }
}
