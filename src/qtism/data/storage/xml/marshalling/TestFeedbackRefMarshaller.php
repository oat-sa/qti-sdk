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

use qtism\data\ShowHide;
use qtism\data\TestFeedbackAccess;
use qtism\data\TestFeedbackRef;
use qtism\data\QtiComponent;
use \DOMElement;

/**
 * Marshalling implementation for testFeedbackRef extended QTI class.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class TestFeedbackRefMarshaller extends Marshaller
{
    /**
     * Marshall a TestFeedbackRef object to its XML counterpart.
     *
     * @param \qtism\data\QtiComponent $component
     * @return \DOMElement
     */
    public function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('testFeedbackRef');
        
        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        self::setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        self::setDOMElementAttribute($element, 'access', TestFeedbackAccess::getNameByConstant($component->getAccess()));
        self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));
        self::setDOMElementAttribute($element, 'href', $component->getHref());

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its TestFeedbackRef data model representation.
     *
     * @param \DOMElement $element
     * @return \qtism\data\QtiComponent A TestFeedbackRef object.
     * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException If the element cannot be unmarshalled.
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
                            
                            $component = new TestFeedbackRef($identifier, $outcomeIdentifier, $access, $showHide, $href);
                            
                            return $component;
                            
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

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'testFeedbackRef';
    }
}
