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
 * Copyright (c) 2013-2014 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\content\Stylesheet;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for stylesheet.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class StylesheetMarshaller extends Marshaller
{
    /**
	 * Marshall a Stylesheet object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A Stylesheet object.
	 * @return \DOMElement The according DOMElement object.
	 */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());

        self::setDOMElementAttribute($element, 'href', $component->getHref());
        self::setDOMElementAttribute($element, 'media', $component->getMedia());
        self::setDOMElementAttribute($element, 'type', $component->getType());

        if (($title = $component->getTitle()) != '') {
            self::setDOMElementAttribute($element, 'title', $component->getTitle());
        }

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to a QTI stylesheet element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\QtiComponent A Stylesheet object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException If the mandatory attribute 'href' is missing from $element.
	 */
    protected function unmarshall(DOMElement $element)
    {
        // href is a mandatory value, retrieve it first.
        if (($value = $this->getDOMElementAttributeAs($element, 'href', 'string')) !== null) {
            $object = new Stylesheet($value);

            if (($value = $this->getDOMElementAttributeAs($element, 'type', 'string')) !== null) {
                $object->setType($value);
            }

            if (($value = $this->getDOMElementAttributeAs($element, 'media', 'string')) !== null) {
                $object->setMedia($value);
            }

            if (($value = $this->getDOMElementAttributeAs($element, 'title', 'string')) !== null) {
                $object->setTitle($value);
            }
        } else {
            $msg = "The mandatory attribute 'href' is missing.";
            throw new UnmarshallingException($msg, $element);
        }

        return $object;
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return 'stylesheet';
    }
}
