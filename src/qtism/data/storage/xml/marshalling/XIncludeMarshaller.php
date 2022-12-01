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
use qtism\data\XInclude;

/**
 * Marshalling/Unmarshalling implementation for Include.
 */
class XIncludeMarshaller extends Marshaller
{
    /**
     * Marshall an XInclude object into a DOMElement object.
     *
     * @param QtiComponent $component An XInclude object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        return self::getDOMCradle()->importNode($component->getXml()->documentElement, true);
    }

    /**
     * Unmarshall a DOMElement object corresponding to a math element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A Math object.
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): XInclude
    {
        $node = $element->cloneNode(true);

        return new XInclude($element->ownerDocument->saveXML($node));
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'include';
    }
}
