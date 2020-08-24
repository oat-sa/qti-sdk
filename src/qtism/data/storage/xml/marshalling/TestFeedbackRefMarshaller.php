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
use qtism\data\ShowHide;
use qtism\data\TestFeedbackAccess;
use qtism\data\TestFeedbackRef;

/**
 * Marshalling implementation for testFeedbackRef extended QTI class.
 */
class TestFeedbackRefMarshaller extends Marshaller
{
    /**
     * Marshall a TestFeedbackRef object to its XML counterpart.
     *
     * @param QtiComponent $component
     * @return DOMElement
     */
    public function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('testFeedbackRef');

        $this->setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        $this->setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        $this->setDOMElementAttribute($element, 'access', TestFeedbackAccess::getNameByConstant($component->getAccess()));
        $this->setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));
        $this->setDOMElementAttribute($element, 'href', $component->getHref());

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its TestFeedbackRef data model representation.
     *
     * @param DOMElement $element
     * @return QtiComponent A TestFeedbackRef object.
     * @throws UnmarshallingException If the element cannot be unmarshalled.
     */
    public function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {
            if (($href = $this->getDOMElementAttributeAs($element, 'href')) !== null) {
                if (($outcomeIdentifier = $this->getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
                    if (($access = $this->getDOMElementAttributeAs($element, 'access')) !== null) {
                        if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide')) !== null) {
                            $access = TestFeedbackAccess::getConstantByName($access);
                            $showHide = ShowHide::getConstantByName($showHide);

                            return new TestFeedbackRef($identifier, $outcomeIdentifier, $access, $showHide, $href);
                        } else {
                            $msg = "The mandatory 'showHide' attribute is missing from element 'testFeedbackRef'.";
                            throw new UnmarshallingException($msg, $element);
                        }
                    } else {
                        $msg = "The mandatory 'access' attribute is missing from element 'testFeedbackRef'.";
                        throw new UnmarshallingException($msg, $element);
                    }
                } else {
                    $msg = "The mandatory 'outcomeIdentifier' attribute is missing from element 'testFeedbackRef'.";
                    throw new UnmarshallingException($msg, $element);
                }
            } else {
                $msg = "The mandatory 'href' attribute is missing from element 'testFeedbackRef'.";
                throw new UnmarshallingException($msg, $element);
            }
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from element 'testFeedbackRef'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    public function getExpectedQtiClassName()
    {
        return 'testFeedbackRef';
    }
}
