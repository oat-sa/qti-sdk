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
 * Copyright (c) 2013-2015 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 * @license GPLv2
 */

namespace qtism\data\storage\xml\marshalling;

use qtism\data\QtiComponent;
use qtism\data\state\ShufflingGroup;
use qtism\common\collections\IdentifierCollection;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for BaseValue.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ShufflingGroupMarshaller extends Marshaller
{
    /**
	 * Marshall a ShufflingGroup object into a DOMElement object.
	 *
	 * @param \qtism\data\QtiComponent $component A ShufflingGroup object.
	 * @return \DOMElement The according DOMElement object.
	 */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());
        self::setDOMElementAttribute($element, 'identifiers', implode("\x20", $component->getIdentifiers()->getArrayCopy()));
        
        $fixedIdentifiers = $component->getFixedIdentifiers();
        if (count($fixedIdentifiers) > 0) {
            self::setDOMElementAttribute($element, 'fixedIdentifiers', implode("\x20", $component->getFixedIdentifiers()->getArrayCopy()));
        }

        return $element;
    }

    /**
	 * Unmarshall a DOMElement object corresponding to a QTI ShufflingGroup element.
	 *
	 * @param \DOMElement $element A DOMElement object.
	 * @return \qtism\data\QtiComponent A ShufflingGroup object.
	 * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException
	 */
    protected function unmarshall(DOMElement $element)
    {
        if (($identifiers = $this->getDOMElementAttributeAs($element, 'identifiers')) !== null) {
            $identifiers = explode("\x20", $identifiers);
            $component = new ShufflingGroup(new IdentifierCollection($identifiers));
            
            if (($fixedIdentifiers = $this->getDOMElementAttributeAs($element, 'fixedIdentifiers')) !== null) {
                $fixedIdentifiers = explode("\x20", $fixedIdentifiers);
                $component->setFixedIdentifiers(new IdentifierCollection($fixedIdentifiers));
            }

            return $component;
        } else {
            $msg = "The mandatory attribute 'identifiers' is missing from element '" . $element->localName . "'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return 'shufflingGroup';
    }
}
