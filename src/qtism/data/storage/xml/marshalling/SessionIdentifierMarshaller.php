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
 * Copyright (c) 2018-2020 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\common\datatypes\QtiIdentifier;
use qtism\common\datatypes\QtiUri;
use qtism\data\QtiComponent;
use qtism\data\results\SessionIdentifier;

/**
 * Class SessionIdentifierMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 */
class SessionIdentifierMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent|SessionIdentifier $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        $element->setAttribute('sourceID', (string)$component->getSourceID());
        $element->setAttribute('identifier', (string)$component->getIdentifier());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI sessionIdentifier element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A QtiComponent object.
     * @throws UnmarshallingException
     */
    protected function unmarshall(DOMElement $element): QtiComponent
    {
        if (!$element->hasAttribute('sourceID')) {
            throw new UnmarshallingException('SessionIdentifier element must have sourceID attribute', $element);
        }
        if (!$element->hasAttribute('identifier')) {
            throw new UnmarshallingException('SessionIdentifier element must have identifier attribute', $element);
        }
        return new SessionIdentifier(
            new QtiUri($element->getAttribute('sourceID')),
            new QtiIdentifier($element->getAttribute('identifier'))
        );
    }

    /**
     * Get the class name/tag name of the QtiComponent/DOMElement which can be handled
     * by the Marshaller's implementation.
     *
     * Return an empty string if the marshaller implementation does not expect a particular
     * QTI class name.
     *
     * @return string A QTI class name or an empty string.
     */
    public function getExpectedQtiClassName(): string
    {
        return 'sessionIdentifier';
    }
}
