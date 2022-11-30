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
use qtism\data\content\interactions\CustomInteraction;
use qtism\data\QtiComponent;
use qtism\data\storage\xml\Utils;

/**
 * Marshalling/Unmarshalling implementation for customInteraction.
 */
class CustomInteractionMarshaller extends Marshaller
{
    /**
     * Marshall a CustomInteraction object into a DOMElement object.
     *
     * @param QtiComponent $component A CustomInteraction object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);
        $this->fillElement($element, $component);
        $this->setDOMElementAttribute($element, 'responseIdentifier', $component->getResponseIdentifier());

        if ($component->hasXmlBase() === true) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        $xml = $component->getXml();
        Utils::importChildNodes($xml->documentElement, $element);
        Utils::importAttributes($xml->documentElement, $element);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI customInteraction element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A CustomInteraction object.
     * @throws UnmarshallingException
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): CustomInteraction
    {
        if (($responseIdentifier = $this->getDOMElementAttributeAs($element, 'responseIdentifier')) !== null) {
            $frag = $element->ownerDocument->createDocumentFragment();
            $element = $element->cloneNode(true);
            $frag->appendChild($element);
            $xmlString = $frag->ownerDocument->saveXML($frag);

            $component = new CustomInteraction($responseIdentifier, $xmlString);
            $this->fillBodyElement($component, $element);
        }

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'customInteraction';
    }
}
