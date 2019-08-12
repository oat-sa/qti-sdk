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
 * Copyright (c) 2018 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Moyon Camille, <camille@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use DOMElement;
use qtism\common\datatypes\QtiIdentifier;
use qtism\data\QtiComponent;
use qtism\data\results\Context;
use qtism\data\results\SessionIdentifierCollection;

/**
 * Class ContextMarshaller
 *
 * The marshaller to manage serialization between QTI component and DOM Element
 * 
 * @package qtism\data\storage\xml\marshalling
 */
class ContextMarshaller extends Marshaller
{
    /**
     * Marshall a QtiComponent object into its QTI-XML equivalent.
     *
     * @param QtiComponent $component A QtiComponent object to marshall.
     * @return DOMElement A DOMElement object.
     * @throws MarshallingException If an error occurs during the marshalling process.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement($this->getExpectedQtiClassName());

        if ($component->hasSourcedId()) {
            $element->setAttribute('sourcedId', $component->getSourcedId());
        }

        if ($component->hasSessionIdentifiers()) {
            foreach ($component->getSessionIdentifiers() as $sessionIdentifier) {
                $sessionIdentifierElement = $this->getMarshallerFactory()
                    ->createMarshaller($sessionIdentifier)
                    ->marshall($sessionIdentifier);
                $element->appendChild($sessionIdentifierElement);
            }
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI context element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A QtiComponent object.
     */
    protected function unmarshall(DOMElement $element)
    {
        $sourcedId = $element->hasAttribute('sourcedId')
            ? new QtiIdentifier($element->getAttribute('sourcedId'))
            : null;

        $sessionIdentifierElements = self::getChildElementsByTagName($element, 'sessionIdentifier');
        if (!empty($sessionIdentifierElements)) {
            $sessionIdentifiers = [];
            foreach ($sessionIdentifierElements as $sessionIdentifierElement) {
                $sessionIdentifiers[] = $this->getMarshallerFactory()
                    ->createMarshaller($sessionIdentifierElement)
                    ->unmarshall($sessionIdentifierElement);
            }
            $sessionIdentifierCollection = new SessionIdentifierCollection($sessionIdentifiers);
        } else {
            $sessionIdentifierCollection = null;
        }

        return new Context($sourcedId, $sessionIdentifierCollection);
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
    public function getExpectedQtiClassName()
    {
        return 'context';
    }
}