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
use qtism\data\QtiComponent;
use qtism\data\rules\Ordering;

/**
 * Marshalling/Unmarshalling implementation for ordering.
 */
class OrderingMarshaller extends Marshaller
{
    /**
     * Marshall an Ordering object into a DOMElement object.
     *
     * @param QtiComponent $component An Ordering object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component): DOMElement
    {
        $element = $this->createElement($component);

        $this->setDOMElementAttribute($element, 'shuffle', $component->getShuffle());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI Ordering element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent An Ordering object.
     */
    #[\ReturnTypeWillChange]
    protected function unmarshall(DOMElement $element): Ordering
    {
        $object = new Ordering();

        if (($value = $this->getDOMElementAttributeAs($element, 'shuffle', 'boolean')) !== null) {
            $object->setShuffle($value);
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName(): string
    {
        return 'ordering';
    }
}
