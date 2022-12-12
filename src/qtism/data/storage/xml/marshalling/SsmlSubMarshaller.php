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
use qtism\data\content\ssml\Sub;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for SSML Sub.
 */
class SsmlSubMarshaller extends Marshaller
{
    /**
     * Marshall an SSML sub object into a DOMElement object.
     *
     * @param QtiComponent $component An SSML sub object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        return self::getDOMCradle()->importNode($component->getXml()->documentElement, true);
    }

    /**
     * Unmarshall a DOMElement object corresponding to an SSML sub element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return Sub An SSML sub object.
     */
    protected function unmarshall(DOMElement $element): Sub
    {
        $node = $element->cloneNode(true);

        return new Sub($element->ownerDocument->saveXML($node));
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'sub';
    }
}
