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
use qtism\common\enums\BaseType;
use qtism\data\QtiComponent;
use qtism\data\storage\Utils as StorageUtils;
use qtism\data\TimeLimits;

/**
 * Marshalling/Unmarshalling implementation for timeLimits.
 */
class TimeLimitsMarshaller extends Marshaller
{
    /**
     * Marshall a TimeLimits object into a DOMElement object.
     *
     * @param QtiComponent $component A TimeLimits object.
     * @return DOMElement The according DOMElement object.
     */
    protected function marshall(QtiComponent $component)
    {
        $element = $this->createElement($component);

        if ($component->hasMinTime() === true) {
            $this->setDOMElementAttribute($element, 'minTime', $component->getMinTime()->getSeconds(true));
        }

        if ($component->hasMaxTime() === true) {
            $this->setDOMElementAttribute($element, 'maxTime', $component->getMaxTime()->getSeconds(true));
        }

        $this->setDOMElementAttribute($element, 'allowLateSubmission', $component->doesAllowLateSubmission());

        return $element;
    }

    /**
     * Unmarshall a DOMElement object corresponding to a QTI timeLimits element.
     *
     * @param DOMElement $element A DOMElement object.
     * @return QtiComponent A TimeLimits object.
     */
    protected function unmarshall(DOMElement $element)
    {
        $object = new TimeLimits();

        if (($value = $this->getDOMElementAttributeAs($element, 'minTime', 'string')) !== null) {
            $object->setMinTime(StorageUtils::stringToDatatype("PT${value}S", BaseType::DURATION));
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'maxTime', 'string')) !== null) {
            $object->setMaxTime(StorageUtils::stringToDatatype("PT${value}S", BaseType::DURATION));
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'allowLateSubmission', 'boolean')) !== null) {
            $object->setAllowLateSubmission($value);
        }

        return $object;
    }

    /**
     * @return string
     */
    public function getExpectedQtiClassName()
    {
        return 'timeLimits';
    }
}
