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
use qtism\data\ItemSessionControl;
use \DOMElement;

/**
 * Marshalling/Unmarshalling implementation for itemSessionControl.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ItemSessionControlMarshaller extends Marshaller
{
    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::marshall()
     */
    protected function marshall(QtiComponent $component)
    {
        $element = static::getDOMCradle()->createElement($component->getQtiClassName());

        $this->setDOMElementAttribute($element, 'maxAttempts', $component->getMaxAttempts());
        $this->setDOMElementAttribute($element, 'showFeedback', $component->mustShowFeedback());
        $this->setDOMElementAttribute($element, 'allowReview', $component->doesAllowReview());
        $this->setDOMElementAttribute($element, 'showSolution', $component->mustShowSolution());
        $this->setDOMElementAttribute($element, 'allowComment', $component->doesAllowComment());
        $this->setDOMElementAttribute($element, 'allowSkipping', $component->doesAllowSkipping());
        $this->setDOMElementAttribute($element, 'validateResponses', $component->mustValidateResponses());

        return $element;
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::unmarshall()
	 */
    protected function unmarshall(DOMElement $element)
    {
        $object = new ItemSessionControl();

        if (($value = $this->getDOMElementAttributeAs($element, 'maxAttempts', 'integer')) !== null) {
            $object->setMaxAttempts($value);
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'showFeedback', 'boolean')) !== null) {
            $object->setShowFeedback($value);
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'allowReview', 'boolean')) !== null) {
            $object->setAllowReview($value);
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'showSolution', 'boolean')) !== null) {
            $object->setShowSolution($value);
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'allowComment', 'boolean')) !== null) {
            $object->setAllowComment($value);
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'allowSkipping', 'boolean')) !== null) {
            $object->setAllowSkipping($value);
        }

        if (($value = $this->getDOMElementAttributeAs($element, 'validateResponses', 'boolean')) !== null) {
            $object->setValidateResponses($value);
        }

        return $object;
    }

    /**
	 * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
	 */
    public function getExpectedQtiClassName()
    {
        return 'itemSessionControl';
    }
}
