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
use qtism\data\content\ModalFeedbackRule;
use \DOMElement;

/**
 * Marshalling implementation for ModalFeedbackRule extended QTI class.
 *
 * @author Jérôme Bogaerts <jerome@taotesting.com>
 *
 */
class ModalFeedbackRuleMarshaller extends Marshaller
{
    /**
     * Marshall a ModalFeedbackRule object to its XML counterpart.
     *
     * @param \qtism\data\QtiComponent $component
     * @return \DOMElement
     */
    public function marshall(QtiComponent $component)
    {
        $element = self::getDOMCradle()->createElement('modalFeedbackRule');
        self::setDOMElementAttribute($element, 'outcomeIdentifier', $component->getOutcomeIdentifier());
        self::setDOMElementAttribute($element, 'showHide', ShowHide::getNameByConstant($component->getShowHide()));
        self::setDOMElementAttribute($element, 'identifier', $component->getIdentifier());
        
        if ($component->hasTitle() === true) {
            self::setDOMElementAttribute($element, 'title', $component->getTitle());
        }

        return $element;
    }

    /**
     * Unmarshall a DOMElement to its ModalFeedbackRule data model representation.
     *
     * @param \DOMElement $element
     * @return \qtism\data\QtiComponent A ModalFeedbackRule object.
     * @throws \qtism\data\storage\xml\marshalling\UnmarshallingException If the 'identifier', 'outcomeIdentifier', 'showHide', or attribute is missing from the XML definition.
     */
    public function unmarshall(DOMElement $element)
    {
        if (($identifier = $this->getDOMElementAttributeAs($element, 'identifier')) !== null) {

            if (($outcomeIdentifier = $this->getDOMElementAttributeAs($element, 'outcomeIdentifier')) !== null) {
                
                if (($showHide = $this->getDOMElementAttributeAs($element, 'showHide', 'string')) !== null) {
                    
                    $showHide = ShowHide::getConstantByName($showHide);
                    $component = new ModalFeedbackRule($outcomeIdentifier, $showHide, $identifier);
                    
                    if (($title = $this->getDOMElementAttributeAs($element, 'title')) !== null) {
                        $component->setTitle($title);
                    }
                    
                    return $component;

                } else {
                    $msg = "The mandatory 'showHide' attribute is missing from element 'modalFeedbackRule'.";
                    throw new UnmarshallingException($msg, $element);
                }

            } else {
                $msg = "The mandatory 'outcomeIdentifier' attribute is missing from element 'modalFeedbackRule'.";
                throw new UnmarshallingException($msg, $element);
            }
            
        } else {
            $msg = "The mandatory 'identifier' attribute is missing from element 'modalFeedbackRule'.";
            throw new UnmarshallingException($msg, $element);
        }
    }

    /**
     * @see \qtism\data\storage\xml\marshalling\Marshaller::getExpectedQtiClassName()
     */
    public function getExpectedQtiClassName()
    {
        return 'modalFeedbackRule';
    }
}
