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
use qtism\data\QtiComponent;
use qtism\data\content\ModalFeedbackRef;
use \DOMElement;

/**
 * Marshalling implementation for ModalFeedbackRef extended QTI class.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ModalFeedbackRefMarshaller extends Marshaller
{
    /**
     * Marshall a ModalFeedbackRef object to its XML counterpart.
     *
     * @param \qtism\data\QtiComponent $component
     * @return \DOMElement
     */
    public function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('modalFeedbackRef');
        self::setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));
        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        self::setDOMElementAttribute($element, 'href', $component->getHref());
        
        if ($component->hasTitle() === true) {
            self::setDOMElementAttribute($element, 'title', $component->getTitle());
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its ModalFeedbackRef data model representation.
     *
     * @param \DOMElement $element
     * @return \qtism\data\QtiComponent A ModalFeedbackRef object.
     * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException If the 'identifier', 'outcomeIdentifier', 'showHide', or 'href' attribute is missing from the XML definition.
     */
    public function unmarshall(DOMElement $element)
    {
        if (($identifier = self::getDOMElementAttributeAs($element, 'identifier')) !== null) {

            if (($outcomeIdentifier = self::getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
                
                if (($showHide = self::getDOMElementAttributeAs($element, 'showHide', 'integer')) !== null) {
                    
                    if (($href = self::getDOMElementAttributeAs($element, 'href')) !== null) {
                        
                        $component = new ModalFeedbackRef($outcomeIdentifier, $showHide, $identifier, $href);
                        
                        if (($title = self::getDOMElementAttributeAs($element, 'title')) !== null) {
                            $component->setTitle($title);
                        }
                        
                        return $component;
                        
                    } else {
                        $msg = "The mandatory 'href' attribute is missing from element 'modalFeedbackRef'.";
                        throw new UnmarshallingException($msg, $element);
                    }
                    
                } else {
                    $msg = "The mandatory 'showHide' attribute is missing from element 'modalFeedbackRef'.";
                    throw new UnmarshallingException($msg, $element);
                }

            } else {
                $msg = "The mandatory 'outcomeIdentifier' attribute is missing from element 'modalFeedbackRef'.";
                throw new UnmarshallingException($msg, $element);
            }
            
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from element 'modalFeedbackRef'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'modalFeedbackRef';
    }
}
