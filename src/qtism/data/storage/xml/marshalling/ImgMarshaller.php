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
use qtism\data\content\xhtml\Img;
use qtism\data\QtiComponent;

/**
 * Marshalling/Unmarshalling implementation for Img.
 */
class ImgMarshaller extends Marshaller
{
    /**
     * Marshall an Img object into a DOMElement object.
     *
     * @param QtiComponent $component An Img object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        /** @var Img $component */
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'src', $component->getSrc());
        $this->setDOMElementAttribute($element, 'alt', $component->getAlt());

        if ($component->hasWidth()) {
            $this->setDOMElementAttribute($element, 'width', $component->getWidth());
        }

        if ($component->hasHeight()) {
            $this->setDOMElementAttribute($element, 'height', $component->getHeight());
        }

        if ($component->hasLongdesc()) {
            $this->setDOMElementAttribute($element, 'longdesc', $component->getLongdesc());
        }

        if ($component->hasXmlBase()) {
            self::setXmlBase($element, $component->getXmlBase());
        }

        $this->fillElement($element, $component);

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to an XHTML img element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent n Img object.
     * @throws UnmarshallingException
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): Img
    {
        $src = $this->getDOMElementAttributeAs($element, 'src');
        if ($src === null) {
            $msg = "The 'mandatory' attribute 'src' is missing from element 'img'.";
            throw new UnmarshallingException($msg, $element);
        }

        // The XSD does not force the 'alt' attribute to be non-empty,
        // thus we consider the 'alt' attribute value as an empty string ('').
        $alt = $this->getDOMElementAttributeAs($element, 'alt') ?? '';

        $component = new Img($src, $alt);

        $longDesc = $this->getDOMElementAttributeAs($element, 'longdesc');
        if ($longDesc !== null) {
            $component->setLongdesc($longDesc);
        }

        $component->setHeight($this->getDOMElementAttributeAs($element, 'height'));
        $component->setWidth($this->getDOMElementAttributeAs($element, 'width'));

        $xmlBase = self::getXmlBase($element);
        if ($xmlBase !== false) {
            $component->setXmlBase($xmlBase);
        }

        $this->fillBodyElement($component, $element);

        return $component;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'img';
    }
}
